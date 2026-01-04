<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// =================================
// CAPTURE & VALIDATE AREA CONTEXT
// =================================
$areaID = $_GET['area'] ?? ($_POST['area'] ?? '');
if (!$areaID) {
    die("Parking area not specified.");
}

// Prevent cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// =================================
// GET AREA INFO
// =================================
$areaStmt = $conn->prepare("
    SELECT AreaCode, AreaName
    FROM parking_area
    WHERE ParkingAreaID = ?
");
$areaStmt->bind_param("s", $areaID);
$areaStmt->execute();
$areaInfo = $areaStmt->get_result()->fetch_assoc();

if (!$areaInfo) {
    die("Invalid parking area.");
}

// =================================
// GET SPACES
// =================================
$stmt = $conn->prepare("
    SELECT 
        ps.ParkingSpaceID,
        ps.SpaceCode,
        ps.SpaceType,
        ss.StatusName
    FROM parking_space ps
    LEFT JOIN space_status ss ON ps.StatusID = ss.StatusID
    WHERE ps.ParkingAreaID = ?
    ORDER BY ps.SpaceCode ASC
");
$stmt->bind_param("s", $areaID);
$stmt->execute();
$res = $stmt->get_result();
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Manage Spaces</title>
<link rel="stylesheet" href="../templates/admin_style.css">

<style>
/* STATUS COLORS */
.status-Available { color:#1a7f37; font-weight:bold; }
.status-Occupied  { color:#c62828; font-weight:bold; }
.status-Reserved  { color:#ef6c00; font-weight:bold; }
.status-Blocked   { color:#6c757d; font-weight:bold; }

/* TOP ACTION BAR */
.action-bar {
    display: flex;
    gap: 20px;
    margin-bottom: 18px;
}

.action-bar .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    color: #fff;
}

/* ADD SPACE */
.btn-add {
    background: #FF7A00;
}
.btn-add:hover {
    background: #e96f00;
}

/* BULK ADD */
.btn-bulk {
    background: #4CAF50;
}
.btn-bulk:hover {
    background: #43a047;
}

/* ROW ACTION BUTTONS */
.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.action-btn {
    padding: 6px 10px;
    font-size: 13px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: 0.2s ease;
}

.action-edit {
    background: #E3F2FD;
    color: #1565C0;
}
.action-edit:hover { background:#BBDEFB; }

.action-block {
    background: #FCE4EC;
    color: #C62828;
}
.action-block:hover { background:#F8BBD0; }

.action-qr {
    background: #E8F5E9;
    color: #2E7D32;
}
.action-qr:hover { background:#C8E6C9; }
</style>
</head>

<body>

<?php include_once('../templates/admin_sidebar.php'); ?>

<div class="main-content">
<div class="page-box">

<header class="header">
🚘 Manage Spaces — Area <?= htmlspecialchars($areaInfo['AreaCode']) ?>
</header>

<div class="box">

<!-- ACTION BAR -->
<div class="action-bar">
    <a class="btn btn-add"
       href="add_space.php?area=<?= urlencode($areaID) ?>">
       ➕ Add Space
    </a>

    <a class="btn btn-bulk"
       href="bulk_add_spaces.php?area=<?= urlencode($areaID) ?>">
       ⚡ Bulk Add Spaces
    </a>
</div>

<table style="margin-top:16px;">
<thead>
<tr>
    <th>Space Code</th>
    <th>Type</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>
<?php if ($res->num_rows > 0): ?>
<?php while ($r = $res->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($r['SpaceCode']) ?></td>
    <td><?= htmlspecialchars($r['SpaceType']) ?></td>
    <td class="status-<?= htmlspecialchars($r['StatusName']) ?>">
        <?= htmlspecialchars($r['StatusName']) ?>
    </td>
    <td>
        <div class="action-buttons">

            <a class="action-btn action-edit"
               href="edit_space.php?id=<?= urlencode($r['ParkingSpaceID']) ?>&area=<?= urlencode($areaID) ?>">
               ✏️ Edit
            </a>

            <a class="action-btn action-block"
               href="block_space.php?id=<?= urlencode($r['ParkingSpaceID']) ?>&area=<?= urlencode($areaID) ?>"
               onclick="return confirm('Block this parking space?')">
               🚫 Block
            </a>

            <a class="action-btn action-qr"
               href="view_space_qr.php?id=<?= urlencode($r['ParkingSpaceID']) ?>&area=<?= urlencode($areaID) ?>">
               🔳 QR
            </a>

        </div>
    </td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="4" style="text-align:center;color:#777;">
        No parking spaces found for this area.
    </td>
</tr>
<?php endif; ?>
</tbody>
</table>

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
