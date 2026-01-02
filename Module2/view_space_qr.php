<?php
require '../config.php';

$spaceID = $_GET['id'] ?? '';
$areaID  = $_GET['area'] ?? '';

if (!$spaceID) {
    header("Location: manage_parking_area.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT QRCodeData, QRImage, GeneratedDate
    FROM space_qr_code
    WHERE ParkingSpaceID = ?
    ORDER BY QRCodeID DESC
    LIMIT 1
");
$stmt->bind_param("s", $spaceID);
$stmt->execute();
$qr = $stmt->get_result()->fetch_assoc();
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Space QR</title>

<link rel="stylesheet" href="../templates/admin_style.css">

<style>
.action-btn {
    display: inline-block;
    padding: 10px 18px;
    background: #FF7A00;
    color: white;
    font-weight: bold;
    border-radius: 8px;
    text-decoration: none;
    transition: 0.2s;
}

.action-btn:hover {
    background: #e56b00;
}

.back-btn {
    background: #777;
}

.back-btn:hover {
    background: #555;
}
</style>
</head>

<body>

<?php include_once('../templates/admin_sidebar.php'); ?>

<div class="main-content">
<div class="page-box">

<header class="header">🔳 Parking Space QR</header>

<div class="box" style="text-align:center">

<?php if ($qr): ?>

    <img src="../uploads/qr/<?= htmlspecialchars($qr['QRImage']) ?>" width="280">

    <p style="margin-top:10px">
        <strong>QR Target:</strong><br>
        <?= htmlspecialchars($qr['QRCodeData']) ?>
    </p>

<?php else: ?>

    <p>No QR generated yet.</p>

    <a href="generate_qr.php?id=<?= urlencode($spaceID) ?>&area=<?= urlencode($areaID) ?>"
       class="action-btn">
       Generate QR
    </a>

<?php endif; ?>

<br><br>

<a href="manage_spaces.php?area=<?= urlencode($areaID) ?>"
   class="action-btn back-btn">
   Back
</a>

</div>
</div>
</div>

</body>
</html>
