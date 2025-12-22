<?php
// checkin.php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Allow only logged-in Student
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

// Get QR token from URL
$code = $_GET['code'] ?? '';

if ($code === '') {
    die("<div style='padding:20px;color:red;font-weight:bold;'>Invalid QR code.</div>");
}

// ================================
// FETCH BOOKING USING bookingqrcode
// ================================
$sql = "
    SELECT 
        b.BookingID,
        b.UserID,
        b.ParkingSpaceID,
        b.BookingDate,
        b.StartTime,
        b.EndTime,
        b.Status,              -- ✅ FIX HERE
        s.StudentProgram,
        ps.SpaceCode,
        pa.AreaName
    FROM bookingqrcode q
    JOIN booking b        ON q.BookingID = b.BookingID
    JOIN student s        ON b.UserID = s.UserID
    JOIN parking_space ps ON b.ParkingSpaceID = ps.ParkingSpaceID
    JOIN parking_area pa  ON ps.ParkingAreaID = pa.ParkingAreaID
    WHERE q.QRCodeData = '$code'
";

$result = mysqli_query($conn, $sql);

// DEBUG SQL ERROR
if (!$result) {
    die("<div style='padding:20px;color:red;font-weight:bold;'>SQL ERROR: " . mysqli_error($conn) . "</div>");
}

$booking = mysqli_fetch_assoc($result);

// If no booking found
if (!$booking) {
    die("<div style='padding:20px;color:red;font-weight:bold;'>Invalid booking QR code.</div>");
}

// =======================================
// BLOCK CHECK-IN IF NOT PENDING
// =======================================
if ($booking['Status'] !== 'Pending') {
    die("<div style='padding:20px;color:red;font-weight:bold;'>
        This booking has already been checked in or completed.
    </div>");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Parking Check-In</title>
    <link rel="stylesheet" href="_Module3CSS.css">
    <link rel="stylesheet" href="../templates/student_style.css">
</head>

<body>
<?php include '../templates/student_sidebar.php'; ?>

<div class="main-content">

    <div class="header">🚘 Parking Check-In</div>

    <div class="form-box">

        <p><strong>Booking ID:</strong> <?= htmlspecialchars($booking['BookingID']) ?></p>
        <p><strong>User ID:</strong> <?= htmlspecialchars($booking['UserID']) ?></p>

        <p><strong>Car Park:</strong>
            <?= htmlspecialchars($booking['AreaName'] . ' - ' . $booking['SpaceCode']) ?>
        </p>

        <p><strong>Booking Time:</strong>
            <?= htmlspecialchars($booking['BookingDate'] . ' ' . $booking['StartTime'] . ' - ' . $booking['EndTime']) ?>
        </p>

        <form method="post" action="save_checkin.php">

            <label>Expected Parking Duration (minutes)</label>
            <input type="number" name="ExpectedDuration" min="15" step="15" required>

            <input type="hidden" name="BookingID" value="<?= htmlspecialchars($booking['BookingID']) ?>">
            <input type="hidden" name="ParkingSpaceID" value="<?= htmlspecialchars($booking['ParkingSpaceID']) ?>">

            <div class="button-row">
                <button type="submit" class="btn-primary">Confirm Check-In</button>
                <a href="booking_list.php" class="btn-cancel">Cancel</a>
            </div>

        </form>
    </div>

</div>

</body>
</html>
