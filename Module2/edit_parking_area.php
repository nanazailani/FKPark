<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: manage_parking_area.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $conn->real_escape_string($_POST['AreaCode']);
    $name = $conn->real_escape_string($_POST['AreaName']);
    $type = $conn->real_escape_string($_POST['AreaType']);
    $desc = $conn->real_escape_string($_POST['AreaDescription']);
    $cap  = intval($_POST['Capacity']);
    $loc  = $conn->real_escape_string($_POST['LocationDesc']);
    $status = $conn->real_escape_string($_POST['StatusID']);

    $stmt = $conn->prepare("
        UPDATE parking_area SET
            AreaCode = ?,
            AreaName = ?,
            AreaType = ?,
            AreaDescription = ?,
            Capacity = ?,
            LocationDesc = ?,
            AreaStatus = ?
        WHERE ParkingAreaID = ?
    ");
    $stmt->bind_param('ssssisss', $code, $name, $type, $desc, $cap, $loc, $status, $id);
    $stmt->execute();

    header('Location: manage_parking_area.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM parking_area WHERE ParkingAreaID = ?");
$stmt->bind_param('s', $id);
$stmt->execute();
$area = $stmt->get_result()->fetch_assoc();
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Edit Parking Area</title>
<link rel="stylesheet" href="../templates/admin_style.css?v=3">

<!-- SAME FORM STYLE AS ADD -->
<style>
.form-grid {
    display: grid;
    grid-template-columns: 160px 1fr;
    row-gap: 14px;
    column-gap: 20px;
    align-items: center;
}

.form-grid label {
    font-weight: 600;
    color: #773f00;
}

.form-grid input,
.form-grid select,
.form-grid textarea {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #FFD7B8;
    font-family: inherit;
}

.form-actions {
    grid-column: 2 / 3;
    margin-top: 15px;
}
</style>
</head>

<body>
<?php include_once('../templates/admin_sidebar.php'); ?>

<div class="main-content">
  <div class="page-box">
    <header class="header">Edit Parking Area</header>

    <div class="box">
      <form method="post" class="form-grid">

        <label>Area Code</label>
        <input type="text" name="AreaCode" value="<?= htmlspecialchars($area['AreaCode']) ?>" required>

        <label>Area Name</label>
        <input type="text" name="AreaName" value="<?= htmlspecialchars($area['AreaName']) ?>" required>

        <label>Area Type</label>
        <select name="AreaType">
          <option <?= $area['AreaType']=='Student'?'selected':'' ?>>Student</option>
          <option <?= $area['AreaType']=='Staff'?'selected':'' ?>>Staff</option>
          <option <?= $area['AreaType']=='Visitor'?'selected':'' ?>>Visitor</option>
        </select>

        <label>Capacity</label>
        <input type="number" name="Capacity" value="<?= $area['Capacity'] ?>" required>

        <label>Location</label>
        <input type="text" name="LocationDesc" value="<?= htmlspecialchars($area['LocationDesc']) ?>">

        <label>Description</label>
        <textarea name="AreaDescription" rows="3"><?= htmlspecialchars($area['AreaDescription']) ?></textarea>

        <label>Status</label>
        <select name="StatusID">
          <option value="Active" <?= $area['AreaStatus']=='Active'?'selected':'' ?>>Active</option>
          <option value="Inactive" <?= $area['AreaStatus']=='Inactive'?'selected':'' ?>>Inactive</option>
        </select>

        <div class="form-actions">
          <button class="btn-success" type="submit">Save</button>
          <br>
          <a class="btn-danger" href="manage_parking_area.php">Cancel</a>
        </div>

      </form>
    </div>

  </div>
</div>
</body>
</html>
