<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// =================================
// CAPTURE & VALIDATE AREA CONTEXT
// =================================
$areaID = $_GET['area'] ?? ($_POST['area'] ?? '');
if (!$areaID) {
    die('Parking area not specified.');
}

// Prevent cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Load areas
$areas = $conn->query("
    SELECT ParkingAreaID, AreaCode, AreaName 
    FROM parking_area 
    ORDER BY AreaCode
");

// Load statuses
$statuses = $conn->query("
    SELECT StatusID, StatusName 
    FROM space_status
");

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $area   = $_POST['ParkingAreaID'];
    $code   = trim($_POST['SpaceCode']);
    $type   = trim($_POST['SpaceType']);
    $status = $_POST['StatusID'];

    // Generate SAFE sequential ParkingSpaceID
    $res = $conn->query("
        SELECT MAX(CAST(SUBSTRING(ParkingSpaceID,3) AS UNSIGNED)) AS maxID
        FROM parking_space
    ");
    $lastNum = $res->fetch_assoc()['maxID'] ?? 0;
    $newId = 'PS' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("
        INSERT INTO parking_space
        (ParkingSpaceID, ParkingAreaID, StatusID, SpaceCode, SpaceType)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $newId, $area, $status, $code, $type);
    $stmt->execute();

    // Return to correct area
    header("Location: manage_spaces.php?area=" . urlencode($areaID));
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Add Parking Space</title>
<link rel="stylesheet" href="../templates/admin_style.css">

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
.form-grid select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #FFD7B8;
}
.form-actions {
    grid-column: 2 / 3;
    margin-top: 20px;
}
</style>
</head>

<body>

<?php include_once('../templates/admin_sidebar.php'); ?>

<div class="main-content">
<div class="page-box">

<header class="header">➕ Add Parking Space</header>

<div class="box">
<form method="post" class="form-grid">

    <!-- 🔒 KEEP AREA CONTEXT -->
    <input type="hidden" name="area" value="<?= htmlspecialchars($areaID) ?>">

    <label>Area</label>
    <select name="ParkingAreaID" required>
        <?php while ($a = $areas->fetch_assoc()): ?>
            <option value="<?= $a['ParkingAreaID'] ?>"
                <?= $a['ParkingAreaID'] == $areaID ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['AreaCode'].' - '.$a['AreaName']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Space Code</label>
    <input type="text" name="SpaceCode" placeholder="A-001" required>

    <label>Space Type</label>
    <input type="text" name="SpaceType" value="Car">

    <label>Status</label>
    <select name="StatusID">
        <?php while ($s = $statuses->fetch_assoc()): ?>
            <option value="<?= $s['StatusID'] ?>">
                <?= htmlspecialchars($s['StatusName']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <div class="form-actions">
        <button class="btn-success" type="submit">Save</button>
        <br><br>
        <a class="btn-danger" href="manage_spaces.php?area=<?= urlencode($areaID) ?>">
            Cancel
        </a>
    </div>

</form>
</div>

</div>
</div>

</body>
</html>
