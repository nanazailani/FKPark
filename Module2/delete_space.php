<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? '';
if ($id) {
    $stmt = $conn->prepare("DELETE FROM parking_space WHERE ParkingSpaceID = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
}
header('Location: manage_spaces.php'); exit;

