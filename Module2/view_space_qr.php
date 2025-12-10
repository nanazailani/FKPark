<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? '';
if (!$id) { header('Location: manage_spaces.php'); exit; }

// fetch most recent QR for this space
$stmt = $conn->prepare("SELECT QRCodeID, QRCodeData, GeneratedDate, GeneratedBy FROM space_qr_code WHERE ParkingSpaceID = ? ORDER BY QRCodeID DESC LIMIT 1");
$stmt->bind_param('s', $id);
$stmt->execute();
$qr = $stmt->get_result()->fetch_assoc();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"/><title>View QR</title>
<link rel="stylesheet" href="../templates/admin_style.css"></head><body>
<?php include_once('../templates/admin_sidebar.php'); ?>
<div class="main-content"><div class="page-box">
  <header class="header">Space QR</header>
  <div class="box">
    <?php if ($qr): ?>
      <p><strong>QR Data:</strong> <?= htmlspecialchars($qr['QRCodeData']) ?></p>
      <p><strong>Generated:</strong> <?= htmlspecialchars($qr['GeneratedDate']) ?> by <?= htmlspecialchars($qr['GeneratedBy']) ?></p>
      <?php
      // check if an image file exists in uploads/qr/ matching qr_<space> pattern
      $files = glob(__DIR__ . '/../uploads/qr/qr_' . $id . '_*.png');
      if ($files && count($files)>0):
          $latest = $files[count($files)-1];
          $rel = str_replace(__DIR__ . '/../', '', $latest);
      ?>
          <p><img src="../<?= htmlspecialchars($rel) ?>" alt="QR" style="max-width:300px;"></p>
      <?php else: ?>
          <p>No image found — show data only. You can regenerate QR to create an image.</p>
      <?php endif; ?>
    <?php else: ?>
      <p>No QR generated for this space yet.</p>
    <?php endif; ?>
    <a class="btn" href="manage_spaces.php">Back</a>
  </div>
</div></div>
</body></html>
