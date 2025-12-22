<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

if (!isset($_GET['plate'])) {
    echo json_encode(["status" => "error", "message" => "No plate given"]);
    exit();
}

$plate = mysqli_real_escape_string($conn, $_GET['plate']);
$plate = str_replace(' ', '', $plate); // WA 1234 B → WA1234B

$sql = "
    SELECT 
        V.VehicleID,
        V.PlateNumber,

        U.UserID,
        U.UserName,

        S.StudentProgram,
        S.StudentYear,
        U.TotalDemeritPoints,
        E.EnforcementType AS EnforcementStatus

    FROM Vehicle V
    LEFT JOIN User U ON V.UserID = U.UserID
    LEFT JOIN Student S ON U.UserID = S.UserID
    LEFT JOIN Enforcement E ON U.UserID = E.UserID AND E.Status = 'Active'

    WHERE TRIM(REPLACE(UPPER(V.PlateNumber), ' ', '')) = TRIM(REPLACE(UPPER('$plate'), ' ', ''))
";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(["status" => "not_found"]);
    exit();
}

echo json_encode([
    "status" => "success",
    "data" => mysqli_fetch_assoc($result)
]);
