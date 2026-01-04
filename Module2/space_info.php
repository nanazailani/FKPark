<?php
require '../config.php';

$spaceID = $_GET['space'] ?? '';
if (!$spaceID) {
    die("Invalid parking space.");
}

$stmt = $conn->prepare("
    SELECT 
        ps.SpaceCode,
        ps.SpaceType,
        ss.StatusName,
        pa.AreaCode,
        pa.AreaName,
        pa.LocationDesc
    FROM parking_space ps
    JOIN parking_area pa ON ps.ParkingAreaID = pa.ParkingAreaID
    JOIN space_status ss ON ps.StatusID = ss.StatusID
    WHERE ps.ParkingSpaceID = ?
");
$stmt->bind_param("s", $spaceID);
$stmt->execute();
$space = $stmt->get_result()->fetch_assoc();

if (!$space) {
    die("Parking space not found.");
}

// Get ALL areas for map
$areas = $conn->query("
    SELECT AreaCode 
    FROM parking_area 
    ORDER BY AreaCode
")->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Parking Space Information</title>

<link rel="stylesheet" href="../templates/public_style.css">

<style>
body {
    background: #f4f8ff;
    font-family: 'Segoe UI', sans-serif;
}

.info-card {
    max-width: 520px;
    margin: 50px auto;
    background: #ffffff;
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 10px 28px rgba(0,0,0,0.12);
}

h2 {
    text-align: center;
    margin-bottom: 24px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px dashed #e0e0e0;
    font-size: 15px;
}

.info-row strong {
    color: #555;
}

.status-pill {
    margin: 24px auto;
    width: fit-content;
    padding: 10px 26px;
    border-radius: 30px;
    font-weight: 700;
    font-size: 16px;
    background: #d9f5dd;
    color: #1f7a1f;
}

/* ===== Parking Area Map ===== */
.map-box {
    margin-top: 28px;
    padding: 20px;
    border-radius: 14px;
    background: #f1f6ff;
}

.map-title {
    font-weight: 700;
    margin-bottom: 14px;
}

.area-map {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 12px;
}

.area-block {
    padding: 14px;
    text-align: center;
    border-radius: 12px;
    font-weight: 600;
    background: #e4ebf5;
    color: #555;
}

.area-block.active {
    background: #4a90e2;
    color: #fff;
    box-shadow: 0 4px 12px rgba(74,144,226,0.4);
}
.footer-note {
    text-align: center;
    font-size: 13px;
    color: #777;
    margin-top: 18px;
}
</style>
</head>

<body>

<div class="info-card">

    <h2>🚗 Parking Space Information</h2>

    <div class="info-row">
        <strong>Space Code</strong>
        <span><?= htmlspecialchars($space['SpaceCode']) ?></span>
    </div>

    <div class="info-row">
        <strong>Area</strong>
        <span><?= htmlspecialchars($space['AreaCode'].' - '.$space['AreaName']) ?></span>
    </div>

    <div class="info-row">
        <strong>Space Type</strong>
        <span><?= htmlspecialchars($space['SpaceType']) ?></span>
    </div>

    <div class="info-row">
        <strong>Location</strong>
        <span><?= htmlspecialchars($space['LocationDesc']) ?></span>
    </div>

    <div class="status-pill">
        <?= htmlspecialchars($space['StatusName']) ?>
    </div>

    <!-- Parking Area Map -->
    <div class="map-box">
        <div class="map-title">🗺 Parking Area Map</div>

        <div class="area-map">
            <?php foreach ($areas as $a): ?>
                <div class="area-block <?= $a['AreaCode'] === $space['AreaCode'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($a['AreaCode']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="footer-note">
        You are currently parked in the highlighted area.
    </div>

</div>

</body>
</html>
