<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Make sure Dompdf is installed via Composer:
// composer require dompdf/dompdf
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

// Security check
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../login.php");
    exit();
}

// ===================== BASIC STATS (same as dashboard) =====================
$today = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM Summon 
    WHERE SummonDate = CURDATE()
"))['total'];

$month = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM Summon 
    WHERE MONTH(SummonDate) = MONTH(CURDATE())
      AND YEAR(SummonDate)  = YEAR(CURDATE())
"))['total'];

$totalSummons = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM Summon
"))['total'];

$totalStudents = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM User WHERE UserRole = 'Student'
"))['total'];

$totalVehicles = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM Vehicle
"))['total'];

$unpaidCount = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM Summon WHERE SummonStatus = 'Unpaid'
"))['total'];

// ===================== SUMMONS PER MONTH =====================
$monthlyCounts = array_fill(1, 12, 0);

$resMonthly = mysqli_query($conn, "
    SELECT MONTH(SummonDate) AS m, COUNT(*) AS total
    FROM Summon
    WHERE YEAR(SummonDate) = YEAR(CURDATE())
    GROUP BY MONTH(SummonDate)
");

while ($row = mysqli_fetch_assoc($resMonthly)) {
    $m = (int)$row['m'];
    $monthlyCounts[$m] = (int)$row['total'];
}

$monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$monthData   = array_values($monthlyCounts);

// ===================== SUMMONS BY VIOLATION (DONUT DATA) =====================
$vioLabels = [];
$vioCounts = [];

$resVio = mysqli_query($conn, "
    SELECT VT.ViolationName, COUNT(*) AS total
    FROM Summon S
    JOIN ViolationType VT ON S.ViolationTypeID = VT.ViolationTypeID
    GROUP BY S.ViolationTypeID
");

while ($row = mysqli_fetch_assoc($resVio)) {
    $vioLabels[] = $row['ViolationName'];
    $vioCounts[] = (int)$row['total'];
}

// ===================== TOP VIOLATORS =====================
$topViolators = [];

$resTop = mysqli_query($conn, "
    SELECT 
        U.UserID,
        U.UserName,
        COUNT(*) AS SummonCount,
        COALESCE(SUM(VT.DemeritPoints),0) AS TotalPoints
    FROM Summon S
    JOIN Vehicle V ON S.VehicleID = V.VehicleID
    JOIN User U    ON V.StudentID = U.UserID
    LEFT JOIN ViolationType VT ON S.ViolationTypeID = VT.ViolationTypeID
    GROUP BY U.UserID, U.UserName
    HAVING SummonCount > 0
    ORDER BY TotalPoints DESC, SummonCount DESC
    LIMIT 5
");

while ($row = mysqli_fetch_assoc($resTop)) {
    $topViolators[] = $row;
}

// ===================== BUILD HTML FOR PDF =====================
$todayDate = date('d M Y, H:i');

ob_start();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>FKPark Security Dashboard Report</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            background: #FAFAFA;
        }

        h1,
        h2,
        h3 {
            margin: 0;
            padding: 0;
            color: #5A4B00;
        }

        .report-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .report-header h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .report-meta {
            font-size: 11px;
            color: #777;
            text-align: center;
            margin-bottom: 15px;
        }

        .stat-grid {
            display: table;
            width: 100%;
            border-spacing: 12px;
            margin-bottom: 15px;
        }

        .stat-box {
            display: table-cell;
            background: #FFF6C7;
            padding: 12px;
            border-radius: 14px;
            border-left: 6px solid #FFE08C;
            text-align: center;
            color: #5A4B00;
            font-weight: 600;
        }

        .stat-box h1 {
            margin: 0;
            font-size: 22px;
            color: #C48F00;
        }

        .section-title {
            font-size: 16px;
            margin: 15px 0 5px 0;
            color: #5A4B00;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 11px;
        }

        th,
        td {
            border: 1px solid #E5D7A5;
            padding: 6px 5px;
        }

        th {
            background: #FFF0B8;
            color: #5A4B00;
        }

        .top-list-table th {
            text-align: left;
        }

        .muted {
            color: #999;
            font-size: 10px;
        }

        .small {
            font-size: 10px;
        }

        .two-col {
            width: 100%;
        }

        .two-col td {
            vertical-align: top;
            width: 50%;
        }
    </style>
</head>

<body>

    <div class="report-header">
        <h1>🛡 FKPark Security Dashboard Report</h1>
        <div class="report-meta">
            Generated on <?= htmlspecialchars($todayDate) ?><br>
            Generated by: Security Staff
        </div>
    </div>

    <!-- BASIC STATS (same boxes as dashboard, simplified for PDF) -->
    <div class="stat-grid">
        <div class="stat-box">
            <h1><?= $today ?></h1>
            Today’s Summons
        </div>
        <div class="stat-box">
            <h1><?= $month ?></h1>
            This Month
        </div>
        <div class="stat-box">
            <h1><?= $totalSummons ?></h1>
            Total Summons
        </div>
        <div class="stat-box">
            <h1><?= $totalStudents ?></h1>
            Total Students
        </div>
        <div class="stat-box">
            <h1><?= $totalVehicles ?></h1>
            Registered Vehicles
        </div>
    </div>

    <!-- Unpaid status -->
    <p class="small">
        <?php if ($unpaidCount > 0): ?>
            🚨 There are <strong><?= $unpaidCount ?></strong> unpaid summons that need attention.
        <?php else: ?>
            ✅ All summons are currently settled. No unpaid summons.
        <?php endif; ?>
    </p>

    <!-- TWO-COLUMN SECTION: Monthly + Violation summary (as tables instead of charts) -->
    <table class="two-col">
        <tr>
            <td>
                <div class="section-title">📊 Summons per Month (<?= date('Y') ?>)</div>
                <table>
                    <tr>
                        <th>Month</th>
                        <th>Total Summons</th>
                    </tr>
                    <?php foreach ($monthLabels as $idx => $label): ?>
                        <tr>
                            <td><?= htmlspecialchars($label) ?></td>
                            <td><?= (int)$monthData[$idx] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </td>
            <td>
                <div class="section-title">🍩 Summons by Violation Type</div>
                <?php if (count($vioLabels) === 0): ?>
                    <p class="small muted">No data available.</p>
                <?php else: ?>
                    <table>
                        <tr>
                            <th>Violation Type</th>
                            <th>Count</th>
                        </tr>
                        <?php foreach ($vioLabels as $i => $name): ?>
                            <tr>
                                <td><?= htmlspecialchars($name) ?></td>
                                <td><?= (int)$vioCounts[$i] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- TOP VIOLATORS -->
    <div class="section-title">🏅 Top Violators (by Demerit Points)</div>
    <?php if (count($topViolators) === 0): ?>
        <p class="small muted">No violators yet.</p>
    <?php else: ?>
        <table class="top-list-table">
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Summons</th>
                <th>Total Demerit Points</th>
            </tr>
            <?php foreach ($topViolators as $idx => $tv): ?>
                <tr>
                    <td><?= $idx + 1 ?></td>
                    <td><?= htmlspecialchars($tv['UserName']) ?></td>
                    <td><?= (int)$tv['SummonCount'] ?></td>
                    <td><?= (int)$tv['TotalPoints'] ?> pts</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <p class="muted">
        This report is generated based on the current FKPark Summon, User, Vehicle, and ViolationType records.
    </p>

</body>

</html>
<?php
$html = ob_get_clean();

// ===================== RENDER PDF =====================
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'FKPark_Security_Dashboard_Report_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
