<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Security check
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] !== 'Administrator') {
    header("Location: dashboard.php");
    exit;
}

$areaID = $_GET['id'] ?? '';

if (!$areaID) {
    header("Location: manage_areas.php");
    exit;
}

// OPTIONAL: prevent deleting area with spaces
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM parking_space 
    WHERE ParkingAreaID = ?
");
$stmt->bind_param("s", $areaID);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['total'];

if ($count > 0) {
    // Redirect with error message
    header("Location: manage_areas.php?error=area_has_spaces");
    exit;
}

// Delete area
$stmt = $conn->prepare("DELETE FROM parking_area WHERE ParkingAreaID = ?");
$stmt->bind_param("s", $areaID);
$stmt->execute();

// Redirect back after delete
header("Location: manage_parking_area.php?deleted=1");
exit;



