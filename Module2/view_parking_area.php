<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['UserRole'])) {
    die("Access denied");
}

$areaID = $_GET['area'] ?? '';
if (!$areaID) {
    die("Parking area not specified.");
}

// Get area info
$areaStmt = $conn->prepare("
    SELECT AreaCode, AreaName 
    FROM parking_area 
    WHERE ParkingAreaID = ?
");
$areaStmt->bind_param("s", $areaID);
$areaStmt->execute();
$area = $areaStmt->get_result()->fetch_assoc();

// Get spaces
$stmt = $conn->prepare("
    SELECT ps.SpaceCode, ss.StatusName
    FROM parking_space ps
    JOIN space_status ss ON ps.StatusID = ss.StatusID
    WHERE ps.ParkingAreaID = ?
    ORDER BY ps.SpaceCode
");
$stmt->bind_param("s", $areaID);
$stmt->execute();
$res = $stmt->get_result();
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>View Parking Area</title>

<?php
// role-based style
if ($_SESSION['UserRole'] === 'Administrator') {
    echo '<link rel="stylesheet" href="../templates/admin_style.css">';
} elseif ($_SESSION['UserRole'] === 'Security Staff') {
    echo '<link rel="stylesheet" href="../templates/security_style.css">';
} else {
    echo '<link rel="stylesheet" href="../templates/student_style.css">';
}
?>

<style>
.space-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 14px;
    margin-top: 20px;
}

.space-box {
    padding: 14px;
    border-radius: 14px;
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    color: #333;
    box-shadow: 0 3px 6px rgba(0,0,0,0.08);
}

.Available { background: #DFF5E1; color: #1A7F37; }
.Occupied  { background: #FADBD8; color: #C62828; }
.Reserved  { background: #FFF3CD; color: #FF8F00; }
.Blocked   { background: #E0E0E0; color: #555; }

.legend {
    display: flex;
    gap: 14px;
    margin-top: 12px;
    font-size: 14px;
}

.legend span {
    display: flex;
    align-items: center;
    gap: 6px;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}
</style>
</head>

<body>

<?php
// sidebar
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

<header class="header">
🅿️ Parking Area <?= htmlspecialchars($area['AreaCode']) ?> – <?= htmlspecialchars($area['AreaName']) ?>
</header>

<div class="box">

<div class="legend">
    <span><div class="dot Available"></div> Available</span>
    <span><div class="dot Occupied"></div> Occupied</span>
    <span><div class="dot Reserved"></div> Reserved</span>
    <span><div class="dot Blocked"></div> Blocked</span>
</div>

<div class="space-grid">
<?php while ($r = $res->fetch_assoc()): ?>
    <div class="space-box <?= htmlspecialchars($r['StatusName']) ?>">
        <?= htmlspecialchars($r['SpaceCode']) ?>
    </div>
<?php endwhile; ?>
</div>

<br>
<a href="daily_availability.php" class="btn">← Back</a>

</div>
</div>
</div>

</body>
</html>
