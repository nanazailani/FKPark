<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// =================================
// CAPTURE & VALIDATE CONTEXT
// =================================
$spaceID = $_GET['id'] ?? ($_POST['id'] ?? '');
$areaID  = $_GET['area'] ?? ($_POST['area'] ?? '');

if (!$spaceID || !$areaID) {
    die('Parking area not specified.');
}

// Prevent cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code   = trim($_POST['SpaceCode']);
    $type   = trim($_POST['SpaceType']);
    $status = $_POST['StatusID'];

    $stmt = $conn->prepare("
        UPDATE parking_space
        SET SpaceCode = ?, SpaceType = ?, StatusID = ?
        WHERE ParkingSpaceID = ?
    ");
    $stmt->bind_param("ssss", $code, $type, $status, $spaceID);
    $stmt->execute();

    header("Location: manage_spaces.php?area=" . urlencode($areaID));
    exit;
}

// Load space info
$stmt = $conn->prepare("SELECT * FROM parking_space WHERE ParkingSpaceID = ?");
$stmt->bind_param("s", $spaceID);
$stmt->execute();
$sp = $stmt->get_result()->fetch_assoc();

if (!$sp) {
    die('Parking space not found.');
}

// Load statuses
$statuses = $conn->query("SELECT StatusID, StatusName FROM space_status");
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Parking Space</title>
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

<header class="header">✏️ Edit Parking Space</header>

<div class="box">
<form method="post" class="form-grid">

    <!-- KEEP CONTEXT -->
    <input type="hidden" name="id" value="<?= htmlspecialchars($spaceID) ?>">
    <input type="hidden" name="area" value="<?= htmlspecialchars($areaID) ?>">

    <label>Space Code</label>
    <input type="text" name="SpaceCode" value="<?= htmlspecialchars($sp['SpaceCode']) ?>" required>

    <label>Space Type</label>
    <input type="text" name="SpaceType" value="<?= htmlspecialchars($sp['SpaceType']) ?>">

    <label>Status</label>
    <select name="StatusID">
        <?php while ($s = $statuses->fetch_assoc()): ?>
            <option value="<?= $s['StatusID'] ?>"
                <?= $s['StatusID'] == $sp['StatusID'] ? 'selected' : '' ?>>
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
