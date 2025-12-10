<?php
require '../config.php';
if (session_status()===PHP_SESSION_NONE) session_start();

$sql = "SELECT ps.ParkingSpaceID, ps.SpaceCode, pa.AreaCode, ss.StatusName
        FROM parking_space ps
        LEFT JOIN parking_area pa ON ps.ParkingAreaID = pa.ParkingAreaID
        LEFT JOIN space_status ss ON ps.StatusID = ss.StatusID
        ORDER BY pa.AreaCode, ps.SpaceCode";
$res = $conn->query($sql);
?>
<!doctype html><html><head><meta charset="utf-8"/><title>Daily Availability</title><link rel="stylesheet" href="module2_style.css"></head><body>
<?php include '../Module1/admin_sidebar.php'; ?>
<div class="main-content"><div class="page-box">
  <h2>Daily Availability</h2>
  <div class="panel">
    <table class="data-table">
      <thead><tr><th>Space Code</th><th>Area</th><th>Status</th></tr></thead>
      <tbody>
      <?php while($r=$res->fetch_assoc()): ?>
        <tr><td><?= htmlspecialchars($r['SpaceCode']) ?></td><td><?= htmlspecialchars($r['AreaCode']) ?></td><td><?= htmlspecialchars($r['StatusName']) ?></td></tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div></div>
</body></html>
