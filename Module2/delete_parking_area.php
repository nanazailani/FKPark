<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? '';
if ($id) {
    // delete spaces first to prevent FK issues
    $stmt = $conn->prepare("DELETE FROM parking_space WHERE ParkingAreaID = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();

    $stmt2 = $conn->prepare("DELETE FROM parking_area WHERE ParkingAreaID = ?");
    $stmt2->bind_param('s', $id);
    $stmt2->execute();
}
header('Location: manage_parking_area.php');
exit;


