<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
//clear cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$areas = $conn->query("SELECT ParkingAreaID, AreaCode, AreaName FROM parking_area ORDER BY AreaCode");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area = $conn->real_escape_string($_POST['ParkingAreaID']);
    $code = $conn->real_escape_string($_POST['SpaceCode']);
    $type = $conn->real_escape_string($_POST['SpaceType']);
    $status = $conn->real_escape_string($_POST['StatusID']);

    $newId = 'PS' . str_pad(rand(1,999),3,'0',STR_PAD_LEFT);

    $stmt = $conn->prepare("
        INSERT INTO parking_space
        (ParkingSpaceID, ParkingAreaID, StatusID, SpaceCode, SpaceType)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('sssss', $newId, $area, $status, $code, $type);
    $stmt->execute();

    header('Location: manage_spaces.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
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
    font-family: inherit;
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

<header class="header">➕🚘 Add Parking Space</header>

<div class="box">
<form method="post" class="form-grid">

    <label>Area</label>
    <select name="ParkingAreaID" required>
        <?php while ($a = $areas->fetch_assoc()): ?>
            <option value="<?= $a['ParkingAreaID'] ?>">
                <?= htmlspecialchars($a['AreaCode'].' - '.$a['AreaName']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Space Code</label>
    <input type="text" name="SpaceCode" required>

    <label>Space Type</label>
    <input type="text" name="SpaceType" value="Car">

    <label>Status</label>
    <select name="StatusID">
        <option value="ST01">ST01 - Available</option>
        <option value="ST02">ST02 - Occupied</option>
        <option value="ST03">ST03 - Reserved</option>
        <option value="ST04">ST04 - Blocked</option>
    </select>

    <div class="form-actions">

        <!-- GREEN BUTTON -->
        <button type="submit"
            style="
                background:#4CAF50;
                color:#fff;
                padding:14px 28px;
                font-size:16px;
                font-weight:700;
                border:none;
                border-radius:14px;
                cursor:pointer;
                box-shadow:0 4px 10px rgba(0,0,0,0.12);
            ">
            Create Space
        </button>

        <br><br>

        <!-- RED BUTTON -->
        <a href="manage_spaces.php"
           style="
                background:#E74C3C;
                color:#fff;
                padding:12px 26px;
                font-size:15px;
                font-weight:600;
                border-radius:12px;
                text-decoration:none;
                display:inline-block;
           ">
            Cancel
        </a>

    </div>

</form>
</div>

</div>
</div>
<script>
            //pageshow - event bila page show. e.g - tekan background
            window.addEventListener("pageshow", function (event) 
            {
                //true kalau the page is cached 
                if (event.persisted) 
                {
                    //page reload
                    window.location.reload();
                }
            });
        </script>
</body>
</html>
