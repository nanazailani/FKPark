<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? '';
if (!$id) { header('Location: manage_spaces.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area = $conn->real_escape_string($_POST['ParkingAreaID']);
    $code = $conn->real_escape_string($_POST['SpaceCode']);
    $type = $conn->real_escape_string($_POST['SpaceType']);
    $status = $conn->real_escape_string($_POST['StatusID']);

    $stmt = $conn->prepare("UPDATE parking_space SET ParkingAreaID=?, SpaceCode=?, SpaceType=?, StatusID=? WHERE ParkingSpaceID=?");
    $stmt->bind_param('sssss', $area, $code, $type, $status, $id);
    $stmt->execute();
    header('Location: manage_spaces.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM parking_space WHERE ParkingSpaceID = ?");
$stmt->bind_param('s', $id);
$stmt->execute();
$sp = $stmt->get_result()->fetch_assoc();
$areas = $conn->query("SELECT ParkingAreaID, AreaCode, AreaName FROM parking_area ORDER BY AreaCode");
$statuses = $conn->query("SELECT StatusID, StatusName FROM space_status");
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"/><title>Edit Space</title><link rel="stylesheet" href="../templates/admin_style.css"></head><body>
<?php include_once('../templates/admin_sidebar.php'); ?>
<div class="main-content"><div class="page-box">
  <header class="header">Edit Space</header>
  <div class="box">
    <form method="post">
      <label>Area
        <select name="ParkingAreaID">
          <?php while($a = $areas->fetch_assoc()): ?>
            <option value="<?= $a['ParkingAreaID'] ?>" <?= $a['ParkingAreaID']==$sp['ParkingAreaID']?'selected':'' ?>><?= htmlspecialchars($a['AreaCode'].' - '.$a['AreaName']) ?></option>
          <?php endwhile; ?>
        </select>
      </label>
      <label>Space Code<input type="text" name="SpaceCode" value="<?= htmlspecialchars($sp['SpaceCode']) ?>"></label>
      <label>Space Type<input type="text" name="SpaceType" value="<?= htmlspecialchars($sp['SpaceType']) ?>"></label>
      <label>Status
        <select name="StatusID">
          <?php while($s = $statuses->fetch_assoc()): ?>
            <option value="<?= $s['StatusID'] ?>" <?= $s['StatusID']==$sp['StatusID']?'selected':'' ?>><?= htmlspecialchars($s['StatusID'].' - '.$s['StatusName']) ?></option>
          <?php endwhile; ?>
        </select>
      </label>
      <button class="btn" type="submit">Save</button>
      <a class="btn outline" href="manage_spaces.php">Cancel</a>
    </form>
  </div>
</div></div>
</body></html>
