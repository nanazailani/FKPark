<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: booking_list.php");
    exit();
}

$BookingID        = $_POST['BookingID'] ?? '';
$StudentID        = $_POST['StudentID'] ?? '';
$ParkingSpaceID   = $_POST['ParkingSpaceID'] ?? '';   // FIXED HERE
$ExpectedDuration = (int)($_POST['ExpectedDuration'] ?? 0);

if ($BookingID === '' || $StudentID === '' || $ParkingSpaceID === '' || $ExpectedDuration <= 0) {
    echo "<script>
            alert('Invalid check-in data.');
            window.history.back();
          </script>";
    exit();
}

// =======================================
// GENERATE LogID (LG001, LG002, ...)
// =======================================
$idQuery = "SELECT MAX(LogID) AS lastID FROM parkinglog";
$idResult = mysqli_query($conn, $idQuery);
$idRow = mysqli_fetch_assoc($idResult);

$num = ($idRow && $idRow['lastID']) ? (int)substr($idRow['lastID'], 2) + 1 : 1;
$LogID = 'LG' . str_pad($num, 3, '0', STR_PAD_LEFT);
$now = date('Y-m-d H:i:s');

// =======================================
// 1️⃣ INSERT INTO ParkingLog
// =======================================
$insertSql = "
    INSERT INTO parkinglog 
    (LogID, BookingID, StudentID, ParkingSpaceID, CheckInTime, ExpectedDuration)
    VALUES 
    ('$LogID', '$BookingID', '$StudentID', '$ParkingSpaceID', '$now', $ExpectedDuration)
";

if (!mysqli_query($conn, $insertSql)) {
    die('Database error (log insert): ' . mysqli_error($conn));
}

// =======================================
// 2️⃣ UPDATE BOOKING STATUS → Active
// =======================================
$updBooking = "
    UPDATE booking 
    SET Status = 'Active'
    WHERE BookingID = '$BookingID'
";
mysqli_query($conn, $updBooking);

// =======================================
// 3️⃣ UPDATE PARKING SPACE STATUS
// your status list:
// ST01 = Available
// ST02 = Occupied
// ST03 = Reserved
// ST04 = OutOfService
// =======================================

$occupiedStatus = 'ST02';  // correct value for OCCUPIED

$updSpace = "
    UPDATE parkingspace 
    SET StatusID = '$occupiedStatus',
        status_updated_at = NOW()
    WHERE ParkingSpaceID = '$ParkingSpaceID'
";

mysqli_query($conn, $updSpace);

// =======================================
// 4️⃣ REDIRECT TO CHECKOUT
// =======================================
header("Location: checkout.php?log=" . urlencode($LogID));
exit();
?>
