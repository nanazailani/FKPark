<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
//clear cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$id = $_GET['id'] ?? '';
if (!$id) { header('Location: manage_spaces.php'); exit; }

$stmt = $conn->prepare("SELECT ps.*, pa.AreaCode, pa.AreaName FROM parking_space ps LEFT JOIN parking_area pa ON ps.ParkingAreaID = pa.ParkingAreaID WHERE ps.ParkingSpaceID = ?");
$stmt->bind_param('s', $id);
$stmt->execute();
$sp = $stmt->get_result()->fetch_assoc();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"/><title>Space Info</title><link rel="stylesheet" href="../templates/admin_style.css"></head><body>
<?php include_once('../templates/admin_sidebar.php'); ?>
<div class="main-content"><div class="page-box">
  <header class="header">Space Info</header>
  <div class="box">
    <?php if ($sp): ?>
      <p><strong>Space Code:</strong> <?= htmlspecialchars($sp['SpaceCode']) ?></p>
      <p><strong>Area:</strong> <?= htmlspecialchars($sp['AreaCode'] . ' - ' . $sp['AreaName']) ?></p>
      <p><strong>Type:</strong> <?= htmlspecialchars($sp['SpaceType']) ?></p>
      <p><strong>Status:</strong> <?= htmlspecialchars($sp['StatusID']) ?></p>
    <?php else: ?>
      <p>Space not found.</p>
    <?php endif; ?>
    <a class="btn" href="manage_spaces.php">Back</a>
  </div>
</div></div>
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
</body></html>
