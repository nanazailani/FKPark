<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
//clear cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$id = $_GET['id'] ?? '';
if (!$id) { header('Location: manage_parking_area.php'); exit; }

$stmt = $conn->prepare("SELECT ParkingAreaID, AreaCode, AreaName, AreaType, AreaDescription, Capacity, LocationDesc, StatusID FROM parking_area WHERE ParkingAreaID = ?");
$stmt->bind_param('s', $id);
$stmt->execute();
$area = $stmt->get_result()->fetch_assoc();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"/><title>View Area</title><link rel="stylesheet" href="../templates/admin_style.css"></head><body>
<?php include_once('../templates/admin_sidebar.php'); ?>
<div class="main-content"><div class="page-box">
  <header class="header">View Parking Area</header>
  <div class="box">
    <?php if ($area): ?>
      <p><strong>Code:</strong> <?= htmlspecialchars($area['AreaCode']) ?></p>
      <p><strong>Name:</strong> <?= htmlspecialchars($area['AreaName']) ?></p>
      <p><strong>Type:</strong> <?= htmlspecialchars($area['AreaType']) ?></p>
      <p><strong>Capacity:</strong> <?= intval($area['Capacity']) ?></p>
      <p><strong>Location:</strong> <?= htmlspecialchars($area['LocationDesc']) ?></p>
      <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($area['AreaDescription'])) ?></p>
    <?php else: ?>
      <p>Area not found.</p>
    <?php endif; ?>
    <a class="btn" href="manage_parking_area.php">Back</a>
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
