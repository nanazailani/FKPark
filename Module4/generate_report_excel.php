<?php
// Enable semua error supaya senang debug masa development
error_reporting(E_ALL);
// Papar error terus di browser (development sahaja)
ini_set('display_errors', 1);

// Include config file untuk database connection
require_once '../config.php';

// Set header supaya browser tahu ini fail Excel
header("Content-Type: application/vnd.ms-excel");
// Nama fail Excel yang akan dimuat turun
header("Content-Disposition: attachment; filename=summons_report.xls");

/*
Query untuk ambil data saman.
JOIN digunakan untuk gabungkan beberapa table:
- Summon (maklumat saman)
- ViolationType (nama kesalahan)
- Vehicle (kenderaan terlibat)
- User (pemilik kenderaan / pelajar)
- Demerit (mata demerit bagi setiap saman)

LEFT JOIN Demerit sebab ada saman yang mungkin belum ada demerit.
Data disusun ikut tarikh saman (yang latest dulu).
*/
$query = mysqli_query($conn, "
    SELECT 
        S.SummonID,
        S.SummonDate,
        S.SummonStatus,
        VT.ViolationName,
        D.DemeritPoints,
        U.UserName
    FROM Summon S
    JOIN ViolationType VT ON S.ViolationTypeID = VT.ViolationTypeID
    JOIN Vehicle V ON S.VehicleID = V.VehicleID
    JOIN User U ON V.UserID = U.UserID
    LEFT JOIN Demerit D ON D.SummonID = S.SummonID
    ORDER BY S.SummonDate DESC
");

// Papar data dalam bentuk jadual HTML
// Excel boleh baca HTML table sebagai spreadsheet
echo "<table border='1'>
<tr><th>ID</th><th>Date</th><th>Status</th><th>Violation</th><th>Demerit</th><th>Student</th></tr>";

// Loop setiap rekod dari database
// Setiap row akan jadi satu baris dalam Excel
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
// Tamat jadual HTML
echo "</table>";
