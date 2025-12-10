<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $conn->real_escape_string($_POST['AreaCode']);
    $name = $conn->real_escape_string($_POST['AreaName']);
    $type = $conn->real_escape_string($_POST['AreaType']);
    $desc = $conn->real_escape_string($_POST['AreaDescription']);
    $cap  = intval($_POST['Capacity']);
    $loc  = $conn->real_escape_string($_POST['LocationDesc']);
    $status = $conn->real_escape_string($_POST['StatusID']);

    $id = 'PA' . str_pad(rand(1,999),3,'0',STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO parking_area (ParkingAreaID, StatusID, AreaCode, AreaName, AreaType, AreaDescription, Capacity, LocationDesc) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssssis', $id, $status, $code, $name, $type, $desc, $cap, $loc);
    $stmt->execute();
    header('Location: manage_parking_area.php');
    exit;
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"/><title>Add Area</title><link rel="stylesheet" href="../templates/admin_style.css"></head><body>
<?php include_once('../templates/admin_sidebar.php'); ?>
<div class="main-content"><div class="page-box">
  <header class="header">Add Parking Area</header>
  <div class="box">
    <form method="post">
      <label>Area Code<input type="text" name="AreaCode" required></label>
      <label>Area Name<input type="text" name="AreaName" required></label>
      <label>Area Type
        <select name="AreaType">
          <option>Student</option>
          <option>Staff</option>
          <option>Visitor</option>
        </select>
      </label>
      <label>Capacity<input type="number" name="Capacity" value="10" required></label>
      <label>Location<input type="text" name="LocationDesc"></label>
      <label>Description<textarea name="AreaDescription"></textarea></label>
      <label>Status
        <select name="StatusID">
          <option value="ST01">ST01 - Available</option>
          <option value="ST02">ST02 - Occupied</option>
          <option value="ST03">ST03 - Reserved</option>
          <option value="ST04">ST04 - Blocked</option>
        </select>
      </label>
      <button class="btn" type="submit">Create Area</button>
      <a class="btn outline" href="manage_parking_area.php">Cancel</a>
    </form>
  </div>
</div></div>
</body></html>
