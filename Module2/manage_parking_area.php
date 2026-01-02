<?php
require '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

/*
    Area-level availability aggregation
*/
$sql = "
SELECT 
    pa.ParkingAreaID,
    pa.AreaCode,
    pa.AreaName,
    pa.AreaType,
    pa.Capacity,
    pa.AreaStatus,
    pa.LocationDesc,

    COALESCE(SUM(
        CASE 
            WHEN ss.StatusName = 'Available' THEN 1 
            ELSE 0 
        END
    ), 0) AS AvailableSpaces

FROM parking_area pa
LEFT JOIN parking_space ps 
    ON pa.ParkingAreaID = ps.ParkingAreaID
LEFT JOIN space_status ss 
    ON ps.StatusID = ss.StatusID

GROUP BY pa.ParkingAreaID
ORDER BY pa.AreaCode ASC
";

$res = $conn->query($sql);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Manage Parking Area</title>
    <link rel="stylesheet" href="../templates/admin_style.css">
    <style>
.action-group {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.action-btn {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.action-btn.edit {
    background: #FFE0B2;
    color: #8D4B00;
}
.action-btn.edit:hover {
    background: #FFCC80;
}

.action-btn.delete {
    background: #FFCDD2;
    color: #B71C1C;
}
.action-btn.delete:hover {
    background: #EF9A9A;
}

.action-btn.view {
    background: #E3F2FD;
    color: #0D47A1;
}
.action-btn.view:hover {
    background: #BBDEFB;
}
</style>

</head>
<body>

<?php include_once('../templates/admin_sidebar.php'); ?>

<div class="main-content">
    <div class="page-box">

        <header class="header">📍 Manage Parking Areas</header>

        <div class="box">

            <a class="btn"
               href="add_parking_area.php"
               style="background:#FF7A00;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none;">
                + Add New Area
            </a>

            <table style="margin-top:16px;">
                <thead>
                    <tr>
                        <th>Area Code</th>
                        <th>Area Name</th>
                        <th>Type</th>
                        <th>Capacity</th>
                        <th>Available</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($res && $res->num_rows > 0): ?>
                    <?php while ($r = $res->fetch_assoc()): ?>

                        <?php
                            $capacity  = (int)$r['Capacity'];
                            $available = (int)$r['AvailableSpaces'];

                            if ($available == 0) {
                                $availability = "<span style='color:red;font-weight:bold;'>0 / $capacity</span>";
                            } elseif ($available <= 3) {
                                $availability = "<span style='color:#FF7A00;font-weight:bold;'>$available / $capacity</span>";
                            } else {
                                $availability = "$available / $capacity";
                            }
                        ?>

                        <tr>
                            <td><?= htmlspecialchars($r['AreaCode']) ?></td>
                            <td><?= htmlspecialchars($r['AreaName']) ?></td>
                            <td><?= htmlspecialchars($r['AreaType']) ?></td>
                            <td><?= $capacity ?></td>
                            <td><?= $availability ?></td>
                            <td><?= htmlspecialchars($r['AreaStatus']) ?></td>
                            <td>
    <div class="action-group">

        <a class="action-btn edit"
           href="edit_parking_area.php?id=<?= urlencode($r['ParkingAreaID']) ?>">
            ✏️ Edit
        </a>

        <a class="action-btn delete"
           href="delete_parking_area.php?id=<?= urlencode($r['ParkingAreaID']) ?>"
           onclick="return confirm('Delete this parking area?')">
            🗑 Delete
        </a>

        <a class="action-btn view"
           href="manage_spaces.php?area=<?= urlencode($r['ParkingAreaID']) ?>">
            🚗 Spaces
        </a>

    </div>
</td>

                        </tr>

                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">
                            No parking areas found.
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
