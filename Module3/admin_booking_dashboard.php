<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Administrator') {
    header("Location: ../Module1/login.php");
    exit();
}

/* ============================================================
   SUMMARY CARDS
   ============================================================ */
function count_query($conn, $sql) {
    $res = $conn->query($sql)->fetch_row();
    return $res ? $res[0] : 0;
}

$total_bookings     = count_query($conn, "SELECT COUNT(*) FROM booking");
$today_bookings     = count_query($conn, "SELECT COUNT(*) FROM booking WHERE BookingDate = CURDATE()");
$active_bookings    = count_query($conn, "SELECT COUNT(*) FROM booking WHERE Status = 'Active'");
$completed_bookings = count_query($conn, "SELECT COUNT(*) FROM booking WHERE Status = 'Completed'");

/* ============================================================
   REPORT 1 – BOOKINGS PER DAY (Line Chart)
   ============================================================ */
$daily_sql = "
    SELECT BookingDate, COUNT(*) AS Total
    FROM booking
    GROUP BY BookingDate
    ORDER BY BookingDate ASC
";
$daily_res = $conn->query($daily_sql);

$dates = [];
$totals = [];
while ($r = $daily_res->fetch_assoc()) {
    $dates[] = $r['BookingDate'];
    $totals[] = $r['Total'];
}

/* ============================================================
   REPORT 2 – STATUS PIE CHART
   ============================================================ */
$status_sql = "
    SELECT Status, COUNT(*) AS total
    FROM booking
    GROUP BY Status
";
$status_res = $conn->query($status_sql);

$status_labels = [];
$status_values = [];
while ($r = $status_res->fetch_assoc()) {
    $status_labels[] = $r['Status'];
    $status_values[] = $r['total'];
}

/* ============================================================
   REPORT 3 – LAST 20 BOOKINGS
   ============================================================ */
$log_sql = "
    SELECT 
        b.BookingID, b.BookingDate, b.StartTime, b.EndTime, b.Status,
        s.StudentID, s.StudentProgram,
        pa.AreaName, ps.SpaceCode
    FROM booking b
    JOIN student s ON b.StudentID = s.StudentID
    JOIN parkingspace ps ON b.ParkingSpaceID = ps.ParkingSpaceID
    JOIN parkingarea pa ON ps.ParkingAreaID = pa.ParkingAreaID
    ORDER BY CAST(SUBSTRING(b.BookingID, 3) AS UNSIGNED) DESC
    LIMIT 20
";

$log_res = $conn->query($log_sql);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Analytics</title>
    <link rel="stylesheet" href="../templates/admin_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .stat-grid {
            display: flex;
            gap: 20px;
        }
        .stat-card {
            flex: 1;
            background: #FFE2C2;
            padding: 18px;
            border-radius: 18px;
            border-left: 8px solid #FF9A3D;
            text-align: center;
            color: #6A3C00;
        }
        .stat-card h1 { font-size: 34px; color: #FF7A00; margin: 0; }

        .chart-section {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .chart-box {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 18px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border-left: 8px solid #FFA74A;
        }

        .table-box {
            margin-top: 25px;
            background: white;
            padding: 20px;
            border-radius: 18px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border-left: 8px solid #FFB46B;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        th { background: #FFF3E0; color: #6A3C00; }
    </style>
</head>

<body>

<?php include '../templates/admin_sidebar.php'; ?>

<div class="main-content">

    <div class="header">📘 Booking Analytics Dashboard</div>

    <!-- SUMMARY CARDS -->
    <div class="stat-grid">
        <div class="stat-card">
            <h1><?= $total_bookings ?></h1>Total Bookings
        </div>
        <div class="stat-card">
            <h1><?= $today_bookings ?></h1>Today’s Bookings
        </div>
        <div class="stat-card">
            <h1><?= $active_bookings ?></h1>Active Bookings
        </div>
        <div class="stat-card">
            <h1><?= $completed_bookings ?></h1>Completed
        </div>
    </div>

    <!-- CHARTS -->
    <div class="chart-section">
        
        <!-- LINE CHART -->
        <div class="chart-box">
            <h3>📉 Bookings Per Day</h3>
            <canvas id="lineChart"></canvas>
        </div>

        <!-- PIE CHART -->
        <div class="chart-box">
            <h3>🧭 Booking Status Distribution</h3>
            <canvas id="pieChart"></canvas>
        </div>

    </div>

    <!-- TABLE: LATEST BOOKINGS -->
    <div class="table-box">
        <h3>📄 Latest 20 Bookings</h3>

        <table>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Student</th>
                <th>Program</th>
                <th>Area</th>
                <th>Space</th>
                <th>Status</th>
            </tr>

            <?php while ($r = $log_res->fetch_assoc()): ?>
            <tr>
                <td><?= $r['BookingID'] ?></td>
                <td><?= $r['BookingDate'] ?></td>
                <td><?= $r['StartTime'] . " - " . $r['EndTime'] ?></td>
                <td><?= $r['StudentID'] ?></td>
                <td><?= $r['StudentProgram'] ?></td>
                <td><?= $r['AreaName'] ?></td>
                <td><?= $r['SpaceCode'] ?></td>
                <td><?= $r['Status'] ?></td>
            </tr>
            <?php endwhile; ?>

        </table>
    </div>

</div>

<!-- JS CHARTS -->
<script>
/* ===================== LINE CHART ===================== */
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
            label: "Bookings",
            data: <?= json_encode($totals) ?>,
            borderColor: "#FF7A00",
            backgroundColor: "rgba(255,122,0,0.3)",
            fill: true,
            tension: 0.3
        }]
    }
});

/* ===================== PIE CHART ===================== */
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($status_labels) ?>,
        datasets: [{
            data: <?= json_encode($status_values) ?>,
            backgroundColor: ["#4CD964", "#FF3B30", "#FFCC00", "#5AC8FA"]
        }]
    }
});
</script>

</body>
</html>
