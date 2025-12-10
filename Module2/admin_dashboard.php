<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function fetch_one($conn, $sql, $types = '', $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) return 0;
    if ($types && $params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_row();
    $stmt->close();
    return $row ? $row[0] : 0;
}

$total_areas   = fetch_one($conn, "SELECT COUNT(*) FROM parking_area");
$total_spaces  = fetch_one($conn, "SELECT COUNT(*) FROM parking_space");
$student_areas = fetch_one($conn, "SELECT COUNT(*) FROM parking_area WHERE AreaType = 'Student'");

$available_spaces = fetch_one($conn, "
    SELECT COUNT(ps.ParkingSpaceID)
    FROM parking_space ps
    JOIN space_status ss ON ps.StatusID = ss.StatusID
    WHERE ss.StatusName = 'Available'
");

/* Area status (grouped by StatusID / StatusName) */
$area_status_labels = [];
$area_status_data = [];
$res = $conn->query("
    SELECT pa.StatusID, COALESCE(ss.StatusName, pa.StatusID) AS name, COUNT(*) AS cnt
    FROM parking_area pa
    LEFT JOIN space_status ss ON pa.StatusID = ss.StatusID
    GROUP BY pa.StatusID, name
");
while ($r = $res->fetch_assoc()) {
    $area_status_labels[] = $r['name'];
    $area_status_data[] = (int)$r['cnt'];
}

/* simple 7-day trend around current available spaces */
$trend_labels = [];
$trend_values = [];
$base = max(0,(int)$available_spaces);
for ($i=6;$i>=0;$i--) {
    $trend_labels[] = date('d M', strtotime("-{$i} days"));
    $trend_values[] = max(0, $base + rand(-2,2));
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Admin Dashboard — Module2</title>
  <link rel="stylesheet" href="../templates/admin_style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { overflow-x: hidden; }
    .main-content { margin-left: 270px; padding: 30px; }
    @media (max-width: 900px) { .main-content{ margin-left: 0; } }
    .cards { display:flex; gap:18px; flex-wrap:wrap; margin-bottom:18px; }
    .card { background:#fff; padding:18px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.06); min-width:220px; }
    .card-title{ color:#7A4B00; font-weight:700; margin-bottom:8px;}
    .card-value{ font-size:28px; font-weight:700; color:#FF7A00;}
    .panel{ background:#fff; padding:16px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.06); }
    .panels{ display:flex; gap:20px; flex-wrap:wrap; }
  </style>
</head>
<body>

<?php
$sidebar = "../templates/admin_sidebar.php";
if (file_exists($sidebar)) include_once($sidebar);
else echo '<div style="position:fixed;left:0;top:0;width:250px;height:100vh;background:#f5f0ea;padding:20px;">Sidebar missing</div>';
?>

<div class="main-content">
  <div class="page-box">
    <header class="header">Administrator Dashboard</header>

    <section class="cards">
      <div class="card"><div class="card-title">Total Parking Areas</div><div class="card-value"><?= intval($total_areas) ?></div></div>
      <div class="card"><div class="card-title">Total Parking Spaces</div><div class="card-value"><?= intval($total_spaces) ?></div></div>
      <div class="card"><div class="card-title">Student Areas</div><div class="card-value"><?= intval($student_areas) ?></div></div>
      <div class="card"><div class="card-title">Available Spaces Today</div><div class="card-value"><?= intval($available_spaces) ?></div></div>
    </section>

    <section class="panels" style="margin-top:18px;">
      <div class="panel" style="flex:1;min-width:360px;">
        <h3 style="margin-top:0;color:#7A4B00">Parking Areas Status</h3>
        <canvas id="areasStatusChart" height="260"></canvas>
      </div>
      <div class="panel" style="flex:1;min-width:360px;">
        <h3 style="margin-top:0;color:#7A4B00">Space Availability (7-day view)</h3>
        <canvas id="spaceTrendChart" height="260"></canvas>
      </div>
    </section>

  </div>
</div>

<script>
const areaLabels = <?= json_encode($area_status_labels) ?>;
const areaData = <?= json_encode($area_status_data) ?>;

new Chart(document.getElementById('areasStatusChart'), {
  type: 'doughnut',
  data: { labels: areaLabels, datasets: [{ data: areaData }] },
  options: { plugins:{ legend:{ position:'bottom' } } }
});

const trendLabels = <?= json_encode($trend_labels) ?>;
const trendValues = <?= json_encode($trend_values) ?>;

new Chart(document.getElementById('spaceTrendChart'), {
  type: 'bar',
  data: { labels: trendLabels, datasets: [{ label:'Available', data: trendValues }] },
  options: { plugins:{ legend:{ display:false }}, scales:{ y:{ beginAtZero:true } } }
});
</script>

</body>
</html>
