<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$areaFilter = $_GET['area'] ?? '';

$sql = "SELECT ps.ParkingSpaceID, ps.SpaceCode, ps.SpaceType, ps.StatusID, pa.AreaCode, ss.StatusName
        FROM parking_space ps
        LEFT JOIN parking_area pa ON ps.ParkingAreaID = pa.ParkingAreaID
        LEFT JOIN space_status ss ON ps.StatusID = ss.StatusID";
if ($areaFilter) {
    $sql .= " WHERE ps.ParkingAreaID = '" . $conn->real_escape_string($areaFilter) . "'";
}
$sql .= " ORDER BY ps.ParkingSpaceID ASC";
$res = $conn->query($sql);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"/><title>Manage Spaces</title>
<link rel="stylesheet" href="../templates/admin_style.css"></head><body>
<?php include_once('../templates/admin_sidebar.php'); ?>
<div class="main-content"><div class="page-box">
  <header class="header">Manage Spaces</header>
  <div class="box">
    <a class="btn" href="add_space.php" style="background:#FF7A00;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none;">+ Add Space</a>

    <table style="margin-top:16px;">
      <thead><tr><th>Space Code</th><th>Area</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php while ($r = $res->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($r['SpaceCode']) ?></td>
          <td><?= htmlspecialchars($r['AreaCode']) ?></td>
          <td><?= htmlspecialchars($r['SpaceType']) ?></td>
          <td><?= htmlspecialchars($r['StatusName'] ?: $r['StatusID']) ?></td>
          <td>
            <a class="btn" href="edit_space.php?id=<?= urlencode($r['ParkingSpaceID']) ?>">Edit</a>
            <a class="btn small danger" href="delete_space.php?id=<?= urlencode($r['ParkingSpaceID']) ?>" onclick="return confirm('Delete this space?')">Delete</a>
            <a class="btn small" href="generate_qr.php?id=<?= urlencode($r['ParkingSpaceID']) ?>">QR</a>
            <a class="btn small" href="view_space_qr.php?id=<?= urlencode($r['ParkingSpaceID']) ?>">View QR</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>

  </div>
</div></div>
</body></html>
