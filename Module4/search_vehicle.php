<?php
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
        S.TotalDemeritPoints,
        S.EnforcementStatus

    FROM Vehicle V
    LEFT JOIN User U ON V.StudentID = U.UserID
    LEFT JOIN Student S ON V.StudentID = S.StudentID

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
