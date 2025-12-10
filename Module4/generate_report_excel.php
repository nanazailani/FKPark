<?php
require_once '../config.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=summons_report.xls");

$query = mysqli_query($conn, "
    SELECT S.SummonID, S.SummonDate, S.SummonStatus,
           VT.ViolationName, VT.DemeritPoints,
           U.UserName
    FROM Summon S
    JOIN ViolationType VT ON S.ViolationTypeID = VT.ViolationTypeID
    JOIN Vehicle V ON S.VehicleID = V.VehicleID
    JOIN User U ON V.StudentID = U.UserID
    ORDER BY S.SummonDate DESC
");

echo "<table border='1'>
<tr><th>ID</th><th>Date</th><th>Status</th><th>Violation</th><th>Demerit</th><th>Student</th></tr>";

while ($row = mysqli_fetch_assoc($query)) {
    echo "<tr>
        <td>{$row['SummonID']}</td>
        <td>{$row['SummonDate']}</td>
        <td>{$row['SummonStatus']}</td>
        <td>{$row['ViolationName']}</td>
        <td>{$row['DemeritPoints']}</td>
        <td>{$row['UserName']}</td>
    </tr>";
}

echo "</table>";
