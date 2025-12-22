<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Access login only for Student role
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header("Location: ../login.php");
    exit();
}

$BookingID = $_GET['id'] ?? '';

if (!$BookingID) {
    die("<div style='color:red; font-size:18px;'>Invalid Booking ID.</div>");
}

// =====================================
// GET BOOKING DETAILS (FIXED)
// =====================================
$sql = "
    SELECT 
        b.*, 
        s.StudentProgram, 
        ps.SpaceCode, 
        pa.AreaName
    FROM booking b
    JOIN student s ON b.UserID = s.UserID
    JOIN parking_space ps ON b.ParkingSpaceID = ps.ParkingSpaceID
    JOIN parking_area pa ON ps.ParkingAreaID = pa.ParkingAreaID
    WHERE b.BookingID = '$BookingID'
";

$result = mysqli_query($conn, $sql);

// SQL error debugging
if (!$result) {
    die("<div style='color:red;'>SQL ERROR: " . mysqli_error($conn) . "</div>");
}

if (mysqli_num_rows($result) == 0) {
    die("<div style='color:red; font-size:18px;'>Booking not found.</div>");
}

$booking = mysqli_fetch_assoc($result);

// =====================================
// GET QR DATA FROM bookingqrcode TABLE
// =====================================
$qrSQL = "
    SELECT * 
    FROM bookingqrcode 
    WHERE BookingID = '$BookingID'
    LIMIT 1
";

$qrResult = mysqli_query($conn, $qrSQL);
$qrData = mysqli_fetch_assoc($qrResult);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Details</title>
    <link rel="stylesheet" href="_Module3CSS.css">
    <link rel="stylesheet" href="../templates/student_style.css">
</head>

<body>

<?php include '../templates/student_sidebar.php'; ?>

<div class="main-content">

    <div class="header">📋 Booking Details</div>

    <!-- PRINT AREA -->
    <div class="form-box" id="print-area">

        <div class="success-title">Upcoming Booking</div>

        <p><strong>Booking ID:</strong> <?= $booking['BookingID'] ?></p>
        <p><strong>Date:</strong> <?= $booking['BookingDate'] ?></p>
        <p><strong>Time:</strong> <?= $booking['StartTime'] ?> - <?= $booking['EndTime'] ?></p>

        <p><strong>Parking Space:</strong>
            <?= $booking['AreaName'] ?> - <?= $booking['SpaceCode'] ?>
        </p>

        <p><strong>Your QR Code:</strong></p>

        <div class="qr-box">
            <?php
                $qrPath = "qr_codes/qr_" . $booking['BookingID'] . ".png";

                if (file_exists($qrPath)) {
                    echo "<img src='$qrPath' width='220'>";
                } else {
                    echo "<p style='color:red;'>QR Code not found.</p>";
                }
            ?>
        </div>

        <p style="margin-top:15px;">
            Scan this QR when arriving at the parking space.
        </p>

        <!-- ACTION BUTTONS -->
        <div class="button-row">
            <button class="print-btn" onclick="window.print()">🖨 Print QR Code</button>

            <a href="booking_list.php">
                <button class="btn-primary">📋 View My Bookings</button>
            </a>
        </div>

    </div>

</div>

</body>
</html>
