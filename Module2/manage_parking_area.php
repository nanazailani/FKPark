<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
//clear cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Updated SQL: removed StatusID + removed JOIN + added AreaStatus
$sql = "SELECT 
            pa.ParkingAreaID, 
            pa.AreaCode, 
            pa.AreaName, 
            pa.AreaType, 
            pa.Capacity, 
            pa.AreaStatus, 
            pa.LocationDesc
        FROM parking_area pa
        ORDER BY pa.AreaCode ASC";

$res = $conn->query($sql);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Manage Parking Area</title>
    <link rel="stylesheet" href="../templates/admin_style.css">
</head>
<body>

<?php include_once('../templates/admin_sidebar.php'); ?>

<div class="main-content">
    <div class="page-box">
        <header class="header">Manage Parking Areas</header>

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
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php while ($r = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['AreaCode']) ?></td>
                        <td><?= htmlspecialchars($r['AreaName']) ?></td>
                        <td><?= htmlspecialchars($r['AreaType']) ?></td>
                        <td><?= intval($r['Capacity']) ?></td>

                        <!-- Updated: show AreaStatus -->
                        <td><?= htmlspecialchars($r['AreaStatus']) ?></td>

                        <td>
                            <a class="btn" 
                               href="edit_parking_area.php?id=<?= urlencode($r['ParkingAreaID']) ?>">
                               Edit
                            </a>

                            <a class="btn small danger" 
                               href="delete_parking_area.php?id=<?= urlencode($r['ParkingAreaID']) ?>" 
                               onclick="return confirm('Delete this area?')">
                               Delete
                            </a>

                            <a class="btn small" 
                               href="manage_spaces.php?area=<?= urlencode($r['ParkingAreaID']) ?>">
                               Spaces
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>

            </table>
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
