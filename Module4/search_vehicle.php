<?php


header('Content-Type: application/json');
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
        COALESCE((
            SELECT SUM(VT.ViolationPoints)
            FROM Summon S2
            LEFT JOIN ViolationType VT ON S2.ViolationTypeID = VT.ViolationTypeID
            LEFT JOIN Vehicle V2 ON S2.VehicleID = V2.VehicleID
            WHERE V2.UserID = U.UserID
        ), 0) AS TotalDemeritPoints,
        P.PunishmentType AS EnforcementStatus

    FROM Vehicle V
    LEFT JOIN User U ON V.UserID = U.UserID
    LEFT JOIN Student S ON U.UserID = S.UserID
    LEFT JOIN PunishmentDuration P 
        ON U.UserID = P.UserID AND P.Status = 'Active'

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
exit();
