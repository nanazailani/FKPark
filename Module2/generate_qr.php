<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$areaID = $_GET['area'] ?? ($_POST['area'] ?? '');

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$spaceID = $_GET['id'] ?? '';
$areaID  = $_GET['area'] ?? '';

if (!$spaceID) {
    header("Location: manage_parking_area.php");
    exit;
}

// ===================================
// SPACE QR → SPACE INFO PAGE
// ===================================
$qrData = "http://localhost/FKPark/Module2/space_info.php?space=" . urlencode($spaceID);

$date = date('Y-m-d H:i:s');
$by   = $_SESSION['UserID'] ?? 'system';

// ===================================
// QR IMAGE STORAGE
// ===================================
$savePath = __DIR__ . '/../uploads/qr/';
if (!is_dir($savePath)) {
    mkdir($savePath, 0755, true);
}

$pngName = 'space_qr_' . $spaceID . '_' . time() . '.png';
$pngFile = $savePath . $pngName;

// ===================================
// GENERATE QR IMAGE
// ===================================
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrData);
$imageData = @file_get_contents($qrUrl);

if ($imageData === false) {
    die("QR generation failed.");
}

file_put_contents($pngFile, $imageData);

// ===================================
// SAVE TO DATABASE
// ===================================
$stmt = $conn->prepare("
    INSERT INTO space_qr_code
    (ParkingSpaceID, QRCodeData, QRImage, GeneratedDate, GeneratedBy)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("sssss", $spaceID, $qrData, $pngName, $date, $by);
$stmt->execute();

// ===================================
// REDIRECT
// ===================================
header(
    "Location: view_space_qr.php?id=" . urlencode($spaceID) .
    "&area=" . urlencode($areaID)
);
exit;
