<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// access login only for Student role
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header("Location: ../login.php");
    exit();
}

$BookingID = $_GET['id'] ?? '';

if (!$BookingID) {
    die("<div style='padding:20px;color:red;font-weight:bold;'>Invalid Booking ID.</div>");
}

// Get booking record
$sql = "SELECT * FROM booking WHERE BookingID = '$BookingID'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("<div style='padding:20px;color:red;'>SQL Error: " . mysqli_error($conn) . "</div>");
}

$booking = mysqli_fetch_assoc($result);

if (!$booking || $booking['Status'] != "Pending") {
    die("<div style='padding:20px;color:red;font-weight:bold;'>Booking not found or cannot be edited.</div>");
}

// Load parking spaces using correct table names
$spacesSql = "
    SELECT ps.ParkingSpaceID, ps.SpaceCode, pa.AreaName
    FROM parking_space ps
    JOIN parking_area pa ON ps.ParkingAreaID = pa.ParkingAreaID
    ORDER BY pa.AreaName, ps.SpaceCode
";
$spaces = mysqli_query($conn, $spacesSql);

if (!$spaces) {
    die("<div style='padding:20px;color:red;'>SQL Error: " . mysqli_error($conn) . "</div>");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Booking</title>
    <link rel="stylesheet" href="_Module3CSS.css">
    <link rel="stylesheet" href="../templates/student_style.css">
</head>

<body>

<?php include '../templates/student_sidebar.php'; ?>

<div class="main-content">
    <div class="header">✏️ Edit Parking Booking</div>

    <div class="form-box">

        <form action="update_booking.php" method="post">

            <input type="hidden" name="BookingID" value="<?= $booking['BookingID'] ?>">

            <label>Booking Date</label>
            <input type="date" name="BookingDate" value="<?= $booking['BookingDate'] ?>" required>

            <label>Start Time</label>
            <input type="time" name="StartTime" value="<?= $booking['StartTime'] ?>" required>

            <label>End Time</label>
            <input type="time" name="EndTime" value="<?= $booking['EndTime'] ?>" required>

            <label>Parking Space</label>
            <select name="ParkingSpaceID" required>
                <?php while ($s = mysqli_fetch_assoc($spaces)): ?>
                    <option value="<?= $s['ParkingSpaceID'] ?>"
                        <?= ($s['ParkingSpaceID'] == $booking['ParkingSpaceID']) ? 'selected' : '' ?>>
                        <?= $s['AreaName'] . " - " . $s['SpaceCode'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Update Booking</button>

            <button class="btn-danger" type="button" onclick="window.location='booking_list.php'">
                Cancel
            </button>
        </form>

    </div>
</div>

</body>
</html>
