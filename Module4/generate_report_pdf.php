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
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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
        COALESCE(SUM(VT.ViolationPoints),0) AS TotalPoints
    FROM Summon S
    JOIN Vehicle V ON S.VehicleID = V.VehicleID
    JOIN User U    ON V.UserID = U.UserID
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
            font-family: Helvetica, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 30px;
            background: #FFFDF5;
            color: #4A3B00;
        }

        .report-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #F1D97A;
        }

        .report-header h1 {
            font-size: 24px;
            margin-bottom: 6px;
            color: #8A6A00;
            letter-spacing: 0.5px;
        }

        h2,
        h3 {
            margin: 0;
            padding: 0;
            color: #5A4B00;
        }

        .stat-grid {
            display: table;
            width: 100%;
            border-spacing: 15px;
            margin: 20px 0;
        }

        .stat-box {
            display: table-cell;
            background: linear-gradient(135deg, #FFF3C4, #FFE28A);
            padding: 16px;
            border-radius: 18px;
            text-align: center;
            color: #6B5200;
            font-weight: bold;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
        }

        .stat-box h1 {
            margin: 0;
            font-size: 26px;
            color: #B58300;
        }

        .section-title {
            font-size: 17px;
            margin: 18px 0 8px 0;
            color: #8A6A00;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 11px;
            background: #FFFFFF;
        }

        th {
            background: #FFE9A8;
            color: #6B5200;
            padding: 8px;
            border: 1px solid #E8D27C;
        }

        td {
            padding: 7px;
            border: 1px solid #E8D27C;
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
        <h1>FKPark Security Dashboard Report</h1>
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
            There are <strong><?= $unpaidCount ?></strong> unpaid summons that need attention.
        <?php else: ?>
            All summons are currently settled. No unpaid summons.
        <?php endif; ?>
    </p>

    <!-- TWO-COLUMN SECTION: Monthly + Violation summary (as tables instead of charts) -->
    <table class="two-col">
        <tr>
            <td>
                <div class="section-title">Summons per Month (<?= date('Y') ?>)</div>
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
                <div class="section-title">Summons by Violation Type</div>
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
    <div class="section-title">Top Violators (by Demerit Points)</div>
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
    <script>
        //pageshow - event bila page show. e.g - tekan background
        window.addEventListener("pageshow", function(event) {
            //true kalau the page is cached 
            if (event.persisted) {
                //page reload
                window.location.reload();
            }
        });
    </script>
</body>

</html>
<?php
$html = ob_get_clean();

// ===================== RENDER PDF =====================
$options = new Options();
$options->setDefaultFont('Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'FKPark_Security_Dashboard_Report_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
