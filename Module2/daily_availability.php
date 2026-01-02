<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Allow all logged-in users
if (!isset($_SESSION['UserRole'])) {
    die("Access denied");
}

// No cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Get summary per area + status
$sql = "
SELECT 
    pa.AreaCode,
    ss.StatusName,
    COUNT(ps.ParkingSpaceID) AS total
FROM parking_space ps
JOIN parking_area pa ON ps.ParkingAreaID = pa.ParkingAreaID
JOIN space_status ss ON ps.StatusID = ss.StatusID
GROUP BY pa.AreaCode, ss.StatusName
ORDER BY pa.AreaCode
";
$res = $conn->query($sql);

// Organize data by area
$areas = [];
while ($row = $res->fetch_assoc()) {
    $areas[$row['AreaCode']][$row['StatusName']] = $row['total'];
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Daily Parking Availability</title>

<?php
// Role-based CSS
if ($_SESSION['UserRole'] === 'Administrator') {
    echo '<link rel="stylesheet" href="../templates/admin_style.css">';
} elseif ($_SESSION['UserRole'] === 'Security Staff') {
    echo '<link rel="stylesheet" href="../templates/security_style.css">';
} else {
    echo '<link rel="stylesheet" href="../templates/student_style.css">';
}
?>

<style>
.area-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.area-card {
    background: #fcfbfbff;
    border-radius: 18px;
    padding: 18px;
    cursor: default;               /* NOT clickable */
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-left: 6px solid #ccc;
}

.area-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 12px;
}

.status-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
    font-size: 14px;
}

.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 12px;
}

.Available { background: #d4f8d4; color: #256029; }
.Reserved  { background: #dce8ff; color: #1f3c88; }
.Occupied  { background: #ffe0b3; color: #8a4b00; }
.Blocked   { background: #ffd6d6; color: #8b0000; }
</style>
</head>

<body>

<?php
if ($_SESSION['UserRole'] === 'Administrator') {
    include '../templates/admin_sidebar.php';
} elseif ($_SESSION['UserRole'] === 'Security Staff') {
    include '../templates/security_sidebar.php';
} else {
    include '../templates/student_sidebar.php';
}
?>

<div class="main-content">
<div class="page-box">

<header class="header">📊 Daily Parking Availability</header>

<div class="box">
<div class="area-grid">

<?php foreach ($areas as $areaCode => $statuses): ?>
<div class="area-card">

    <div class="area-title">🚗 Area <?= htmlspecialchars($areaCode) ?></div>

    <?php
    $allStatuses = ['Available', 'Reserved', 'Occupied', 'Blocked'];
    foreach ($allStatuses as $status):
        $count = $statuses[$status] ?? 0;
    ?>
    <div class="status-row">
        <span class="badge <?= $status ?>"><?= $status ?></span>
        <strong><?= $count ?></strong>
    </div>
    <?php endforeach; ?>

</div>
<?php endforeach; ?>

</div>
</div>

</div>
</div>

</body>
</html>
