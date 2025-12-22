<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Allow only Student role
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header("Location: ../login.php");
    exit();
}

$LogID = $_GET['log'] ?? '';

if ($LogID === '') {
    die("<div style='padding:20px;color:red;font-weight:bold;'>Invalid parking log.</div>");
}

// ===========================================
// FIXED QUERY — JOIN IKUT DATABASE SEBENAR
// ===========================================
$sql = "
    SELECT 
        l.*,
        b.BookingID,
        b.ParkingSpaceID,
        ps.SpaceCode,
        pa.AreaName
    FROM parkinglog l
    JOIN booking b        ON l.BookingID = b.BookingID
    JOIN parking_space ps ON b.ParkingSpaceID = ps.ParkingSpaceID
    JOIN parking_area pa  ON ps.ParkingAreaID = pa.ParkingAreaID
    WHERE l.LogID = '$LogID'
";

$result = mysqli_query($conn, $sql);

// SQL error debug
if (!$result) {
    die("<div style='padding:20px;color:red;'>SQL ERROR: " . mysqli_error($conn) . "</div>");
}

$log = mysqli_fetch_assoc($result);

if (!$log) {
    die("<div style='padding:20px;color:red;font-weight:bold;'>Parking log not found.</div>");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Parking Checkout</title>
    <link rel="stylesheet" href="_Module3CSS.css">
    <link rel="stylesheet" href="../templates/student_style.css">
</head>
<body>

<?php include '../templates/student_sidebar.php'; ?>

<div class="main-content">
    <div class="header">🚗 Parking Checkout</div>

    <div class="form-box">

        <p><strong>Space:</strong> <?= $log['AreaName'] . ' - ' . $log['SpaceCode'] ?></p>
        <p><strong>Check-In Time:</strong> <?= $log['CheckInTime'] ?></p>

        <form method="post" action="save_checkout.php"
              onsubmit="return confirm('Are you sure you want to check out?');">

            <input type="hidden" name="LogID" value="<?= $log['LogID'] ?>">
            <input type="hidden" name="ParkingSpaceID" value="<?= $log['ParkingSpaceID'] ?>">
            <input type="hidden" name="BookingID" value="<?= $log['BookingID'] ?>">

            <div class="button-row">
                <button type="submit" class="btn-primary">Confirm Checkout</button>
                <a href="booking_list.php" class="btn-cancel">Cancel</a>
            </div>
        </form>

    </div>
</div>

</body>
</html>
