<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$sql = "SELECT ps.SpaceCode, pa.AreaCode, ss.StatusName
        FROM parking_space ps
        LEFT JOIN parking_area pa ON ps.ParkingAreaID = pa.ParkingAreaID
        LEFT JOIN space_status ss ON ps.StatusID = ss.StatusID
        ORDER BY pa.AreaCode, ps.SpaceCode";

$res = $conn->query($sql);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Daily Availability</title>

<!-- USE THE SAME ADMIN STYLE -->
<link rel="stylesheet" href="../templates/admin_style.css">
</head>

<body>

<!-- CORRECT SIDEBAR INCLUDE -->
<?php include_once('../templates/admin_sidebar.php'); ?>

<div class="main-content">
<div class="page-box">

<header class="header">Daily Availability</header>

<div class="box">
  <table>
    <thead>
      <tr>
        <th>Space Code</th>
        <th>Area</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($r = $res->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['SpaceCode']) ?></td>
        <td><?= htmlspecialchars($r['AreaCode']) ?></td>
        <td><?= htmlspecialchars($r['StatusName']) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</div>
</div>

</body>
</html>
