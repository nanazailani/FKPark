<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: manage_spaces.php'); exit; }
$id = $_POST['id'] ?? '';
$status = $_POST['status'] ?? '';
if ($id && $status) {
    $stmt = $conn->prepare("UPDATE parking_space SET StatusID = ? WHERE ParkingSpaceID = ?");
    $stmt->bind_param('ss', $status, $id);
    $stmt->execute();
}
header('Location: manage_spaces.php');
exit;
