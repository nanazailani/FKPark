<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Allow only Students
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header("Location: ../login.php");
    exit();
}

$BookingID = $_GET['id'] ?? '';

if (!$BookingID) {
    header("Location: booking_list.php");
    exit();
}

// 1. Update Booking Status → Cancelled
$updateSql = "
    UPDATE Booking 
    SET Status = 'Cancelled' 
    WHERE BookingID = '$BookingID'
";
mysqli_query($conn, $updateSql);


// 2. Delete QR record from bookingqrcode table
$deleteQR = "
    DELETE FROM bookingqrcode
    WHERE BookingID = '$BookingID'
";
mysqli_query($conn, $deleteQR);


// 3. Delete the QR image from folder
$qrFile = "qr_codes/qr_" . $BookingID . ".png";

if (file_exists($qrFile)) {
    unlink($qrFile);
}


// 4. Success Popup + Redirect
echo "<script>
        alert('❌ Booking $BookingID has been cancelled and QR removed.');
        window.location.href = 'booking_list.php';
      </script>";
exit();
?>
