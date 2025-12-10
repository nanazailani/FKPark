<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';


if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../Module1/login.php");
    exit();
}

// ===================== BASIC STATS =====================
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
// Prepare an array for 12 months
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

// Labels for JS (Jan, Feb, …)
$monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$monthData   = array_values($monthlyCounts);

// ===================== SUMMONS BY VIOLATION (DONUT) =====================
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

// ===================== TOP VIOLATORS (BY DEMERIT POINTS) =====================
$topViolators = [];

$resTop = mysqli_query($conn, "
    SELECT 
        U.UserID,
        U.UserName,
        COUNT(*) AS SummonCount,
        COALESCE(SUM(VT.ViolationPoints),0) AS TotalPoints
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

?>
<!DOCTYPE html>
<html>

<head>
    <title>Security Dashboard</title>
    <link rel="stylesheet" href="../templates/security_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .stat-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-box {
            flex: 1;
            min-width: 180px;
            background: #FFF6C7;
            padding: 18px;
            border-radius: 18px;
            border-left: 8px solid #FFE08C;
            text-align: center;
            color: #5A4B00;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .stat-box h1 {
            margin: 0;
            font-size: 32px;
            color: #C48F00;
        }

        .stat-box:nth-child(1) {
            animation-delay: 0.05s;
        }

        .stat-box:nth-child(2) {
            animation-delay: 0.10s;
        }

        .stat-box:nth-child(3) {
            animation-delay: 0.15s;
        }

        .stat-box:nth-child(4) {
            animation-delay: 0.20s;
        }

        .stat-box:nth-child(5) {
            animation-delay: 0.25s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-box {
            padding: 15px 20px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .alert-unpaid {
            background: #FFE1E1;
            border-left: 8px solid #FF6B6B;
            color: #8A2323;
        }

        .alert-ok {
            background: #E7FAD9;
            border-left: 8px solid #4CAF50;
            color: #255C2A;
        }

        .chart-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-card {
            flex: 1;
            min-width: 280px;
            background: #FFFFFF;
            padding: 20px;
            border-radius: 18px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border-left: 8px solid #FFE28A;
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .chart-card h2 {
            margin-top: 0;
            color: #5A4B00;
        }

        .top-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .top-list li {
            padding: 8px 0;
            border-bottom: 1px solid #F2E4B5;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .top-name {
            font-weight: 600;
            color: #5A4B00;
        }

        .top-points {
            font-weight: 700;
            color: #C48F00;
        }

        .top-summons {
            font-weight: 500;
            color: #8A7A2E;
            margin-left: 8px;
        }
    </style>
</head>

<body>

    <?php include '../templates/security_sidebar.php'; ?>

    <div class="main-content">
        <div class="header">🛡 Security Staff Dashboard</div>


        <!-- UNPAID ALERT -->
        <?php if ($unpaidCount > 0): ?>
            <div class="alert-box alert-unpaid">
                🚨 There are <strong><?= $unpaidCount ?></strong> unpaid summons that need attention.
            </div>
        <?php else: ?>
            <div class="alert-box alert-ok">
                ✅ All summons are currently settled. No unpaid summons.
            </div>
        <?php endif; ?>

        <!-- STATISTICS -->
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

        <!-- CHARTS -->
        <div class="chart-grid">
            <!-- Bar Chart -->
            <div class="chart-card" style="animation-delay:0.1s;">
                <h2>📊 Summons per Month (2025)</h2>
                <canvas id="summonMonthChart"></canvas>
            </div>

            <!-- Donut Chart -->
            <div class="chart-card" style="animation-delay:0.15s;">
                <h2>🍩 Summons by Violation Type</h2>
                <canvas id="violationChart"></canvas>
            </div>
        </div>

        <!-- TOP VIOLATORS -->
        <div class="chart-card" style="animation-delay:0.2s; width: 95%;">
            <h2>🏅 Top Violators (by Demerit Points)</h2>

            <?php if (count($topViolators) === 0): ?>
                <p style="color:#8A7A2E;">No violators yet. 😇</p>
            <?php else: ?>
                <ul class="top-list">
                    <?php foreach ($topViolators as $tv): ?>
                        <li>
                            <span class="top-name">
                                <?= htmlspecialchars($tv['UserName']) ?>
                                <span class="top-summons">(<?= $tv['SummonCount'] ?> summons)</span>
                            </span>
                            <span class="top-points"><?= $tv['TotalPoints'] ?> pts</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div style="margin: 25px 0; text-align:center;">
            <form action="generate_report_pdf.php" method="POST" style="display:inline-block; margin-right:10px;">
                <button type="submit"
                    style="
                background:#FFF6C7;
                color:#5A4B00;
                padding:14px 22px;
                font-weight:600;
                border:none;
                border-radius:18px;
                cursor:pointer;
                box-shadow:0 3px 10px rgba(0,0,0,0.08);
                border-left:8px solid #FFE08C;
                font-size:16px;
            ">
                    📄 Download PDF Report
                </button>
            </form>
            <form action="generate_report_excel.php" method="POST" style="display:inline-block;">
                <button type="submit"
                    style="
                background:#FFF6C7;
                color:#5A4B00;
                padding:14px 22px;
                font-weight:600;
                border:none;
                border-radius:18px;
                cursor:pointer;
                box-shadow:0 3px 10px rgba(0,0,0,0.08);
                border-left:8px solid #FFE08C;
                font-size:16px;
            ">
                    📊 Download Excel Report
                </button>
            </form>
        </div>

    </div>

    <script>
        // Auto-refresh every 30 seconds (for "real-time" stats)
        setInterval(function() {
            location.reload();
        }, 30000);

        // ===== BAR CHART: Summons per Month =====
        const monthLabels = <?= json_encode($monthLabels); ?>;
        const monthData = <?= json_encode($monthData); ?>;

        const ctxMonth = document.getElementById('summonMonthChart').getContext('2d');
        new Chart(ctxMonth, {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Summons',
                    data: monthData,
                    backgroundColor: '#FFC93C',
                    borderColor: '#E0A300',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // ===== DONUT CHART: Summons by Violation Type =====
        const vioLabels = <?= json_encode($vioLabels); ?>;
        const vioData = <?= json_encode($vioCounts); ?>;

        const ctxVio = document.getElementById('violationChart').getContext('2d');
        new Chart(ctxVio, {
            type: 'doughnut',
            data: {
                labels: vioLabels,
                datasets: [{
                    data: vioData,
                    backgroundColor: [
                        '#FFE28A',
                        '#FFB8B8',
                        '#B8E1FF',
                        '#C8F7C5',
                        '#E0BBFF',
                        '#FFD3A5'
                    ],
                    borderColor: '#FFFFFF',
                    borderWidth: 2
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

</body>

</html>