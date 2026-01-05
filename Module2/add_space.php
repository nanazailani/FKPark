<?php
require '../config.php';
// Enable semua error supaya senang debug masa development
error_reporting(E_ALL);
// Papar error terus di browser (development sahaja)
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) session_start();

// =================================
// CAPTURE & VALIDATE AREA CONTEXT
// =================================
$areaID = $_GET['area'] ?? ($_POST['area'] ?? '');
if (!$areaID) {
    die('Parking area not specified.');
}

// Prevent cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// =================================
// CHECK AREA SPACE LIMIT
// =================================
$limitStmt = $conn->prepare("
    SELECT 
        pa.Capacity,
        COUNT(ps.ParkingSpaceID) AS currentSpaces
    FROM parking_area pa
    LEFT JOIN parking_space ps 
        ON pa.ParkingAreaID = ps.ParkingAreaID
    WHERE pa.ParkingAreaID = ?
    GROUP BY pa.Capacity
");
$limitStmt->bind_param("s", $areaID);
$limitStmt->execute();
$limitResult = $limitStmt->get_result()->fetch_assoc();

$maxSpaces = $limitResult['Capacity'] ?? 0;
$currentSpaces = $limitResult['currentSpaces'] ?? 0;
// Load areas
$areas = $conn->query("
    SELECT ParkingAreaID, AreaCode, AreaName 
    FROM parking_area 
    ORDER BY AreaCode
");

// Load statuses
$statuses = $conn->query("
    SELECT StatusID, StatusName 
    FROM space_status
");

// =================================
// HANDLE FORM SUBMIT
// =================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($currentSpaces >= $maxSpaces) {
        echo "<script>
            alert('❌ Cannot add space. This parking area has reached its maximum capacity.');
            window.location.href = 'manage_spaces.php?area=" . addslashes($areaID) . "';
        </script>";
        exit;
    }

    $area   = $_POST['ParkingAreaID'];
    $code   = trim($_POST['SpaceCode']);
    $type   = trim($_POST['SpaceType']);
    $status = $_POST['StatusID'];

    // Generate SAFE sequential ParkingSpaceID
    $res = $conn->query("
        SELECT MAX(CAST(SUBSTRING(ParkingSpaceID,3) AS UNSIGNED)) AS maxID
        FROM parking_space
    ");
    $lastNum = $res->fetch_assoc()['maxID'] ?? 0;
    $newId = 'PS' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("
        INSERT INTO parking_space
        (ParkingSpaceID, ParkingAreaID, StatusID, SpaceCode, SpaceType)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $newId, $area, $status, $code, $type);
    $stmt->execute();

    header("Location: manage_spaces.php?area=" . urlencode($areaID));
    exit;
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Add Parking Space</title>
    <link rel="stylesheet" href="../templates/admin_style.css">

    <style>
        .form-grid {
            display: grid;
            grid-template-columns: 120px 1fr;
            /* ✅ MORE space for inputs */
            gap: 14px 24px;
            /* nicer spacing */
            align-items: center;
            width: 110%;
            box-sizing: border-box;
        }

        .form-grid label {
            font-weight: 600;
            color: #773f00;
            text-align: right;
            padding-right: 10px;
        }

        .form-grid input,
        .form-grid select,
        .form-grid textarea {
            width: 90%;
            padding: 12px 10px;
            /* ✅ slightly taller & wider feel */
            border-radius: 10px;
            border: 1px solid #FFD7B8;
            box-sizing: border-box;
        }

        .form-actions {
            grid-column: 2 / 3;
            margin-top: 20px;
        }

        /* form box */
        .box {
            width: 150%;
            max-width: 1100px;
            margin-left: 1px;
            padding: 30px;
            box-sizing: border-box;
        }
    </style>
</head>

<body>

    <?php include_once('../templates/admin_sidebar.php'); ?>

    <div class="main-content">
        <div class="page-box">

            <header class="header">➕ Add Parking Space</header>

            <div class="box">
                <form method="post" class="form-grid">

                    <!-- 🔒 KEEP AREA CONTEXT -->
                    <input type="hidden" name="area" value="<?= htmlspecialchars($areaID) ?>">

                    <label>Area</label>
                    <select name="ParkingAreaID" required>
                        <?php while ($a = $areas->fetch_assoc()): ?>
                            <option value="<?= $a['ParkingAreaID'] ?>"
                                <?= $a['ParkingAreaID'] == $areaID ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['AreaCode'] . ' - ' . $a['AreaName']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label>Space Code</label>
                    <input type="text" name="SpaceCode" placeholder="A-001" required>

                    <label>Space Type</label>
                    <input type="text" name="SpaceType" value="Car">

                    <label>Status</label>
                    <select name="StatusID">
                        <?php while ($s = $statuses->fetch_assoc()): ?>
                            <option value="<?= $s['StatusID'] ?>">
                                <?= htmlspecialchars($s['StatusName']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <div class="form-actions">
                        <button class="btn-success" type="submit">Save</button>
                        <br><br>
                        <a class="btn-danger" href="manage_spaces.php?area=<?= urlencode($areaID) ?>">
                            Cancel
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>

</body>

</html>