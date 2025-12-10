<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? '';
if (!$id) { header('Location: manage_spaces.php'); exit; }

$code = 'BOOK-SP-' . $id . '-' . substr(md5(uniqid('', true)),0,8);
$date = date('Y-m-d H:i:s');
$by = $_SESSION['UserID'] ?? 'system';

// insert DB row
$stmt = $conn->prepare("INSERT INTO space_qr_code (ParkingSpaceID, QRCodeData, GeneratedDate, GeneratedBy) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $id, $code, $date, $by);
$ok = $stmt->execute();

// try to fetch QR image from Google Chart API and save it
$savePath = __DIR__ . '/../uploads/qr/';
if (!is_dir($savePath)) mkdir($savePath, 0755, true);

$pngName = 'qr_' . $id . '_' . time() . '.png';
$pngFile = $savePath . $pngName;

// URL-encode data
$qrUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($code) . '&chld=L|1';
$imageData = @file_get_contents($qrUrl);
if ($imageData !== false) {
    file_put_contents($pngFile, $imageData);
    // Optionally, update the DB row to store filename (if you have a column). Here we keep QRCodeData.
}

header('Location: view_space_qr.php?id=' . urlencode($id));
exit;

