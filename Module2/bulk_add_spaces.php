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

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// =================================
// HANDLE FORM SUBMISSION
// =================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $prefix = strtoupper(trim($_POST['Prefix'])); // A, B, C
    $start  = intval($_POST['StartNumber']);      // 1
    $qty    = intval($_POST['Quantity']);         // 20
    $type   = trim($_POST['SpaceType']);          

    if ($qty <= 0 || $start <= 0) {
        die('Invalid quantity or start number.');
    }

    // Default AVAILABLE status
    $availableStatusID = 'ST01';

    // =================================
    // GET LAST SPACE ID SAFELY
    // =================================
    $res = $conn->query("
        SELECT MAX(CAST(SUBSTRING(ParkingSpaceID,3) AS UNSIGNED)) AS maxID
        FROM parking_space
    ");
    $lastNum = $res->fetch_assoc()['maxID'] ?? 0;

    // Prepare insert
    $stmt = $conn->prepare("
        INSERT INTO parking_space
        (ParkingSpaceID, ParkingAreaID, SpaceCode, SpaceType, StatusID)
        VALUES (?, ?, ?, ?, ?)
    ");

    // =================================
    // BULK INSERT LOOP
    // =================================
    for ($i = 0; $i < $qty; $i++) {

        $lastNum++;
        $newID = 'PS' . str_pad($lastNum, 4, '0', STR_PAD_LEFT);

        $spaceNo   = $start + $i;
        $spaceCode = $prefix . '-' . str_pad($spaceNo, 3, '0', STR_PAD_LEFT);

        $stmt->bind_param(
            "sssss",
            $newID,
            $areaID,
            $spaceCode,
            $type,
            $availableStatusID
        );

        $stmt->execute();
    }

    // Return to correct area
    header("Location: manage_spaces.php?area=" . urlencode($areaID));
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Bulk Add Spaces</title>
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
    margin-top: 18px;
}
</style>
</head>

<body>

<?php include_once('../templates/admin_sidebar.php'); ?>

<div class="main-content">
<div class="page-box">

<header class="header">⚡ Bulk Add Parking Spaces</header>

<div class="box">
<form method="post" class="form-grid">

    <!-- KEEP AREA CONTEXT -->
    <input type="hidden" name="area" value="<?= htmlspecialchars($areaID) ?>">

    <label>Prefix</label>
    <input type="text" name="Prefix" placeholder="A" maxlength="2" required>

    <label>Start Number</label>
    <input type="number" name="StartNumber" value="1" min="1" required>

    <label>Quantity</label>
    <input type="number" name="Quantity" value="10" min="1" required>

    <label>Space Type</label>
    <select name="SpaceType">
        <option value="Car">Car</option>
        <option value="Motorcycle">Motorcycle</option>
    </select>

    <div class="form-actions">
        <button class="btn-success" type="submit">Create Spaces</button>
        <br><br>
        <a class="btn-danger"
           href="manage_spaces.php?area=<?= urlencode($areaID) ?>">
           Cancel
        </a>
    </div>

</form>
</div>

</div>
</div>

<script>
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>

</body>
</html>
