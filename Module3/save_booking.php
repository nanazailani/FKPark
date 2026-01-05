<?php
session_start();
// Enable semua error supaya senang debug masa development
error_reporting(E_ALL);
// Papar error terus di browser (development sahaja)
ini_set('display_errors', 1);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';
require_once 'phpqrcode/qrlib.php';

// ===============================
// Validate POST request
// ===============================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: booking_form.php");
    exit();
}

// ===============================
// GET USER ID
// ===============================
$UserID = $_SESSION['UserID'];

$SpaceID     = $_POST['SpaceID'];
$BookingDate = $_POST['BookingDate'];
$StartTime   = $_POST['StartTime'];
$EndTime     = $_POST['EndTime'];


// ===============================
// 1. CHECK BOOKING CLASH
// ===============================
$clashSql = "
    SELECT *
    FROM booking
    WHERE ParkingSpaceID = '$SpaceID'
      AND BookingDate = '$BookingDate'
      AND Status IN ('Pending','Active')
      AND (
            (StartTime <= '$StartTime' AND EndTime > '$StartTime') OR
            (StartTime < '$EndTime' AND EndTime >= '$EndTime') OR
            ('$StartTime' <= StartTime AND '$EndTime' >= EndTime)
          )
";

$clashResult = mysqli_query($conn, $clashSql);

if (mysqli_num_rows($clashResult) > 0) {
    echo "<script>
            alert('❌ Booking clash detected! Please choose another time or space.');
            window.history.back();
          </script>";
    exit();
}


// ===============================
// 2. GENERATE BOOKING ID
// ===============================
$idQuery = "SELECT MAX(BookingID) AS lastID FROM booking";
$idResult = mysqli_query($conn, $idQuery);
$idRow = mysqli_fetch_assoc($idResult);

$num = ($idRow['lastID']) ? intval(substr($idRow['lastID'], 2)) + 1 : 1;
$BookingID = 'BK' . str_pad($num, 3, '0', STR_PAD_LEFT);


// ===============================
// 3. INSERT BOOKING
// ===============================
$insertBooking = "
    INSERT INTO booking
    (BookingID, UserID, ParkingSpaceID, BookingDate, StartTime, EndTime, Status, CreatedAt)
    VALUES 
    ('$BookingID', '$UserID', '$SpaceID', '$BookingDate', '$StartTime', '$EndTime', 'Pending', NOW())
";

if (!mysqli_query($conn, $insertBooking)) {
    die('Database Error (Booking Insert): ' . mysqli_error($conn));
}


// ===============================
// 4. GENERATE QR TOKEN
// ===============================
$qrToken = 'BOOK-' . $BookingID . '-' . bin2hex(random_bytes(4));
$qrURL   = "http://localhost/FKPark/Module3/checkin.php?code=" . urlencode($qrToken);


// ===============================
// 5. GENERATE QR IMAGE
// ===============================
$qrFolder = __DIR__ . "/qr_codes/";
if (!file_exists($qrFolder)) {
    mkdir($qrFolder, 0777, true);
}

$qrFile = $qrFolder . "qr_" . $BookingID . ".png";
QRcode::png($qrURL, $qrFile, QR_ECLEVEL_L, 10);


// ===============================
// 6. INSERT QR CODE (FIXED)
// ===============================
$insertQR = "
    INSERT INTO bookingqrcode
    (BookingID, QRCodeData, GeneratedDate)
    VALUES
    ('$BookingID', '$qrToken', NOW())
";

if (!mysqli_query($conn, $insertQR)) {
    die('Database Error (QRCode Insert): ' . mysqli_error($conn));
}


// ===============================
// 7. REDIRECT
// ===============================
header("Location: booking_success.php?id=" . urlencode($BookingID));
exit();
