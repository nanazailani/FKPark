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

$BookingID        = $_POST['BookingID'];
$BookingDate      = $_POST['BookingDate'];
$StartTime        = $_POST['StartTime'];
$EndTime          = $_POST['EndTime'];
$ParkingSpaceID   = $_POST['ParkingSpaceID'];   // <-- FIXED

// ============================================
// CLASH CHECK (exclude current booking)
// ============================================
$clashSql = "
    SELECT *
    FROM booking
    WHERE ParkingSpaceID = '$ParkingSpaceID'
      AND BookingDate = '$BookingDate'
      AND Status IN ('Pending','Active')
      AND BookingID <> '$BookingID'
      AND (
            (StartTime <= '$StartTime' AND EndTime > '$StartTime') OR
            (StartTime < '$EndTime' AND EndTime >= '$EndTime') OR
            ('$StartTime' <= StartTime AND '$EndTime' >= EndTime)
          )
";

$clashResult = mysqli_query($conn, $clashSql);

if (mysqli_num_rows($clashResult) > 0) {
  echo "<script>
            alert('❌ Booking clash detected. Please choose another time or space.');
            window.history.back();
          </script>";
  exit();
}

// ============================================
// UPDATE BOOKING
// ============================================
$updSql = "
    UPDATE booking
    SET BookingDate     = '$BookingDate',
        StartTime       = '$StartTime',
        EndTime         = '$EndTime',
        ParkingSpaceID  = '$ParkingSpaceID'
    WHERE BookingID = '$BookingID'
      AND Status = 'Pending'
";

mysqli_query($conn, $updSql);

// ============================================
// SUCCESS
// ============================================
echo "<script>
        alert('✅ Booking {$BookingID} updated successfully!');
        window.location.href = 'booking_list.php';
      </script>";
exit();
