<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
//clear cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$id = $_GET['id'] ?? '';
if (!$id) { header('Location: manage_spaces.php'); exit; }

$stmt = $conn->prepare("
    SELECT QRCodeData, QRImage, GeneratedDate, GeneratedBy 
    FROM space_qr_code 
    WHERE ParkingSpaceID = ? 
    ORDER BY QRCodeID DESC LIMIT 1
");
$stmt->bind_param('s', $id);
$stmt->execute();
$qr = $stmt->get_result()->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Space QR</title>
<link rel="stylesheet" href="../templates/admin_style.css">
<style>
.print-box{text-align:center;}
@media print {
  body * { visibility: hidden; }
  .print-box, .print-box * { visibility: visible; }
  .print-box { position:absolute; left:0; top:0; width:100%; }
}
</style>
</head>
<body>

<?php include_once('../templates/admin_sidebar.php'); ?>

<div class="main-content">
<div class="page-box">
<header class="header">Space QR</header>

<div class="box print-box">
<?php if ($qr): ?>
    <p><strong>QR Data:</strong> <?= htmlspecialchars($qr['QRCodeData']) ?></p>
    <p><strong>Generated:</strong> <?= htmlspecialchars($qr['GeneratedDate']) ?> by <?= htmlspecialchars($qr['GeneratedBy']) ?></p>

    <?php if ($qr['QRImage'] && file_exists(__DIR__ . '/../uploads/qr/' . $qr['QRImage'])): ?>
        <img src="../uploads/qr/<?= htmlspecialchars($qr['QRImage']) ?>" style="max-width:300px;">
    <?php else: ?>
        <p>QR image missing. Please regenerate.</p>
    <?php endif; ?>

    <br><br>
    <button class="btn" onclick="window.print()">Print QR</button>
<?php else: ?>
    <p>No QR generated yet.</p>
<?php endif; ?>

<br><br>
<a class="btn outline" href="manage_spaces.php">Back</a>
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
