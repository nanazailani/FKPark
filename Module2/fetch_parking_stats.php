<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$general = (int)$conn->query("SELECT COUNT(*) as c FROM parking_area WHERE AreaType <> 'Student'")->fetch_assoc()['c'];
$bookable = (int)$conn->query("SELECT COUNT(*) as c FROM parking_area WHERE AreaType = 'Student'")->fetch_assoc()['c'];
$closed_today = 0; // not used in current schema

$available_spaces = (int)$conn->query("
    SELECT COUNT(ps.ParkingSpaceID) FROM parking_space ps
    JOIN space_status ss ON ps.StatusID = ss.StatusID
    WHERE ss.StatusName = 'Available'
")->fetch_row()[0];

$trend = ['labels'=>[], 'values'=>[]];
for ($i=6;$i>=0;$i--) {
    $trend['labels'][] = date('d M', strtotime("-{$i} days"));
    $trend['values'][] = max(0, $available_spaces + rand(-2,2));
}

echo json_encode([
    'general'=>$general,
    'bookable'=>$bookable,
    'closed_today'=>$closed_today,
    'available_spaces'=>$available_spaces,
    'trend'=>$trend
]);
