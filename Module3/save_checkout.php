<?php
// save_checkout.php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: booking_list.php");
    exit();
}

$LogID          = $_POST['LogID'] ?? '';
$ParkingSpaceID = $_POST['ParkingSpaceID'] ?? '';
$BookingID      = $_POST['BookingID'] ?? '';

if ($LogID === '' || $ParkingSpaceID === '') {
    echo "<script>
            alert('Invalid checkout data.');
            window.history.back();
          </script>";
    exit();
}

$now = date('Y-m-d H:i:s');

/* ---------------------------------------
   1️⃣ UPDATE PARKING LOG → Set CheckOutTime
----------------------------------------- */
$updateLog = "
    UPDATE parkinglog
    SET CheckOutTime = '$now'
    WHERE LogID = '$LogID'
";

if (!mysqli_query($conn, $updateLog)) {
    die('Database Error (parkinglog update): ' . mysqli_error($conn));
}

/* ---------------------------------------
   2️⃣ UPDATE BOOKING STATUS → Completed
----------------------------------------- */
if (!empty($BookingID)) {
    $updateBooking = "
        UPDATE booking
        SET Status = 'Completed'
        WHERE BookingID = '$BookingID'
    ";

    if (!mysqli_query($conn, $updateBooking)) {
        die('Database Error (booking update): ' . mysqli_error($conn));
    }
}

/* ---------------------------------------
   3️⃣ FREE PARKING SPACE (make it Available)
----------------------------------------- */

$AVAILABLE_STATUS = 'ST01'; // Available

$updateSpace = "
    UPDATE parking_space
    SET StatusID = '$AVAILABLE_STATUS'
    WHERE ParkingSpaceID = '$ParkingSpaceID'
";

if (!mysqli_query($conn, $updateSpace)) {
    die('Database Error (parking_space update): ' . mysqli_error($conn));
}

/* ---------------------------------------
   4️⃣ SUCCESS MESSAGE + REDIRECT
----------------------------------------- */
echo "<script>
        alert('✅ Checkout successful. Parking space is now available.');
        window.location.href = 'booking_list.php';
      </script>";
exit();
?>
