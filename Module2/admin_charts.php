<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once 'config.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Parking Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="module2_style.css">
</head>

<body>

<?php include 'module2_sidebar.php'; ?>

<div class="content">
    <h2>Module 2 — Parking Analytics</h2>

    <canvas id="chart1" width="400" height="200"></canvas>
    <canvas id="chart2" width="400" height="200"></canvas>
</div>

<script>
fetch("fetch_parking_stats.php")
    .then(res => res.json())
    .then(data => {
        new Chart(document.getElementById("chart1"), {
            type: "bar",
            data: data.areaUsage
        });

        new Chart(document.getElementById("chart2"), {
            type: "pie",
            data: data.spaceStatus
        });
    });
</script>

</body>
</html>
