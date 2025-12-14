<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    $stmt = $conn->prepare("DELETE FROM parking_space WHERE ParkingSpaceID = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to the spaces list for the same area (optional improvement)
$area = $_GET['area'] ?? '';
if (!empty($area)) {
    header("Location: manage_spaces.php?area=" . urlencode($area));
    exit;
}

header('Location: manage_spaces.php');
exit;

