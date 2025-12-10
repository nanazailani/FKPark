<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$areas = $conn->query("SELECT ParkingAreaID, AreaCode, AreaName FROM parking_area ORDER BY AreaCode");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area = $conn->real_escape_string($_POST['ParkingAreaID']);
    $code = $conn->real_escape_string($_POST['SpaceCode']);
    $type = $conn->real_escape_string($_POST['SpaceType']);
    $status = $conn->real_escape_string($_POST['StatusID']);

    $newId = 'PS' . str_pad(rand(1,999),3,'0',STR_PAD_LEFT);
    $stmt = $conn->prepare("INSERT INTO parking_space (ParkingSpaceID, ParkingAreaID, StatusID, SpaceCode, SpaceType) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $newId, $area, $status, $code, $type);
    $stmt->execute();
    header('Location: manage_spaces.php');
    exit;
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"/><title>Add Space</title><link rel="stylesheet" href="../templates/admin_style.css"></head><body>
<?php include_once('../templates/admin_sidebar.php'); ?>
<div class="main-content"><div class="page-box">
  <header class="header">Add Parking Space</header>
  <div class="box">
    <form method="post">
      <label>Area
        <select name="ParkingAreaID" required>
          <?php while ($a = $areas->fetch_assoc()): ?>
            <option value="<?= $a['ParkingAreaID'] ?>"><?= htmlspecialchars($a['AreaCode'] . ' - ' . $a['AreaName']) ?></option>
          <?php endwhile; ?>
        </select>
      </label>
      <label>Space Code<input type="text" name="SpaceCode" required></label>
      <label>Space Type<input type="text" name="SpaceType" value="Car"></label>
      <label>Status
        <select name="StatusID">
          <option value="ST01">ST01 - Available</option>
          <option value="ST02">ST02 - Occupied</option>
          <option value="ST03">ST03 - Reserved</option>
          <option value="ST04">ST04 - Blocked</option>
        </select>
      </label>
      <button class="btn" type="submit">Create Space</button>
      <a class="btn outline" href="manage_spaces.php">Cancel</a>
    </form>
  </div>
</div></div>
</body></html>
