<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? '';
if (!$id) { header('Location: manage_parking_area.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $conn->real_escape_string($_POST['AreaCode']);
    $name = $conn->real_escape_string($_POST['AreaName']);
    $type = $conn->real_escape_string($_POST['AreaType']);
    $desc = $conn->real_escape_string($_POST['AreaDescription']);
    $cap  = intval($_POST['Capacity']);
    $loc  = $conn->real_escape_string($_POST['LocationDesc']);
    $status = $conn->real_escape_string($_POST['StatusID']);

    $stmt = $conn->prepare("UPDATE parking_area SET AreaCode=?, AreaName=?, AreaType=?, AreaDescription=?, Capacity=?, LocationDesc=?, StatusID=? WHERE ParkingAreaID=?");
    $stmt->bind_param('ssssisiss', $code, $name, $type, $desc, $cap, $loc, $status, $id);
    $stmt->execute();
    header('Location: manage_parking_area.php');
    exit;
}

$stmt = $conn->prepare("SELECT ParkingAreaID, AreaCode, AreaName, AreaType, AreaDescription, Capacity, LocationDesc, StatusID FROM parking_area WHERE ParkingAreaID = ?");
$stmt->bind_param('s', $id);
$stmt->execute();
$area = $stmt->get_result()->fetch_assoc();
if (!$area) { header('Location: manage_parking_area.php'); exit; }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"/><title>Edit Area</title><link rel="stylesheet" href="../templates/admin_style.css"></head><body>
<?php include_once('../templates/admin_sidebar.php'); ?>
<div class="main-content"><div class="page-box">
  <header class="header">Edit Parking Area</header>
  <div class="box">
    <form method="post">
      <label>Area Code<input type="text" name="AreaCode" value="<?= htmlspecialchars($area['AreaCode']) ?>" required></label>
      <label>Area Name<input type="text" name="AreaName" value="<?= htmlspecialchars($area['AreaName']) ?>" required></label>
      <label>Area Type
        <select name="AreaType">
          <option <?= $area['AreaType']=='Student'?'selected':'' ?>>Student</option>
          <option <?= $area['AreaType']=='Staff'?'selected':'' ?>>Staff</option>
          <option <?= $area['AreaType']=='Visitor'?'selected':'' ?>>Visitor</option>
        </select>
      </label>
      <label>Capacity<input type="number" name="Capacity" value="<?= intval($area['Capacity']) ?>" required></label>
      <label>Location<input type="text" name="LocationDesc" value="<?= htmlspecialchars($area['LocationDesc']) ?>"></label>
      <label>Description<textarea name="AreaDescription"><?= htmlspecialchars($area['AreaDescription']) ?></textarea></label>
      <label>Status
        <select name="StatusID">
          <option value="ST01" <?= $area['StatusID']=='ST01'?'selected':'' ?>>ST01 - Available</option>
          <option value="ST02" <?= $area['StatusID']=='ST02'?'selected':'' ?>>ST02 - Occupied</option>
          <option value="ST03" <?= $area['StatusID']=='ST03'?'selected':'' ?>>ST03 - Reserved</option>
          <option value="ST04" <?= $area['StatusID']=='ST04'?'selected':'' ?>>ST04 - Blocked</option>
        </select>
      </label>
      <button class="btn" type="submit">Save</button>
      <a class="btn outline" href="manage_parking_area.php">Cancel</a>
    </form>
  </div>
</div></div>
</body></html>
