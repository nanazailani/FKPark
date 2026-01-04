<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id   = $_GET['id'] ?? '';
$area = $_GET['area'] ?? '';


/* Get BLOCKED status */
$sql = "SELECT StatusID FROM space_status WHERE StatusName = 'Blocked' LIMIT 1";
$res = $conn->query($sql);
if ($res->num_rows === 0) die("Blocked status not found.");

$blockedID = $res->fetch_assoc()['StatusID'];

$stmt = $conn->prepare("
    UPDATE parking_space 
    SET StatusID = ? 
    WHERE ParkingSpaceID = ?
");
$stmt->bind_param("ss", $blockedID, $id);
$stmt->execute();

header("Location: manage_spaces.php?area=" . urlencode($area));
exit;
