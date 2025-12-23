<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
//clear cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$id = $_GET['id'] ?? '';
if (!$id) { header('Location: manage_spaces.php'); exit; }

$code = 'BOOK-SP-' . $id . '-' . substr(md5(uniqid('', true)), 0, 8);
$date = date('Y-m-d H:i:s');
$by   = $_SESSION['UserID'] ?? 'system';

// folder
$savePath = __DIR__ . '/../uploads/qr/';
if (!is_dir($savePath)) mkdir($savePath, 0755, true);

// filename
$pngName = 'qr_' . $id . '_' . time() . '.png';
$pngFile = $savePath . $pngName;

// Google QR
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($code);
$imageData = @file_get_contents($qrUrl);

if ($imageData === false) {
    die('Failed to generate QR image.');
}

file_put_contents($pngFile, $imageData);

// store in DB
$stmt = $conn->prepare("
    INSERT INTO space_qr_code 
    (ParkingSpaceID, QRCodeData, QRImage, GeneratedDate, GeneratedBy) 
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param('sssss', $id, $code, $pngName, $date, $by);
$stmt->execute();

header('Location: view_space_qr.php?id=' . urlencode($id));
exit;
