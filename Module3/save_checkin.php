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
$ParkingSpaceID   = $_POST['ParkingSpaceID'] ?? '';
$ExpectedDuration = (int)($_POST['ExpectedDuration'] ?? 0);

if ($BookingID === '' || $ParkingSpaceID === '' || $ExpectedDuration <= 0) {
    echo "<script>alert('Invalid check-in data.'); window.history.back();</script>";
    exit();
}

/* ======================================================
   1. BLOCK DOUBLE CHECK-IN
====================================================== */
$checkSql = "
    SELECT LogID 
    FROM parkinglog 
    WHERE BookingID = '$BookingID'
      AND CheckOutTime IS NULL
    LIMIT 1
";

$checkResult = mysqli_query($conn, $checkSql);

if (mysqli_num_rows($checkResult) > 0) {
    echo "<script>
            alert('You have already checked in for this booking.');
            window.location.href='booking_list.php';
          </script>";
    exit();
}

/* ======================================================
   2. GENERATE LogID
====================================================== */
$idQuery  = "SELECT MAX(LogID) AS lastID FROM parkinglog";
$idResult = mysqli_query($conn, $idQuery);
$idRow    = mysqli_fetch_assoc($idResult);

$num  = ($idRow && $idRow['lastID']) ? (int)substr($idRow['lastID'], 2) + 1 : 1;
$LogID = 'LG' . str_pad($num, 3, '0', STR_PAD_LEFT);
$now   = date('Y-m-d H:i:s');

/* ======================================================
   3. INSERT parkinglog
====================================================== */
$insertSql = "
    INSERT INTO parkinglog
    (LogID, BookingID, CheckInTime, ExpectedDuration)
    VALUES
    ('$LogID', '$BookingID', '$now', $ExpectedDuration)
";

if (!mysqli_query($conn, $insertSql)) {
    die('Database error (log insert): ' . mysqli_error($conn));
}

/* ======================================================
   4. UPDATE booking status → Active
====================================================== */
mysqli_query($conn, "
    UPDATE booking 
    SET Status='Active'
    WHERE BookingID='$BookingID'
");

/* ======================================================
   5. UPDATE parking space status
====================================================== */
mysqli_query($conn, "
    UPDATE parking_space
    SET StatusID='ST02'
    WHERE ParkingSpaceID='$ParkingSpaceID'
");

/* ======================================================
   6. REDIRECT TO CHECKOUT
====================================================== */
header("Location: checkout.php?log=" . urlencode($LogID));
exit();
?>
