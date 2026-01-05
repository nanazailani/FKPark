<?php
// ===============================
// FILE: add_parking_area.php
// PURPOSE: Add a new parking area
// ===============================

require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Prevent cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code   = trim($_POST['AreaCode']);
    $name   = trim($_POST['AreaName']);
    $type   = trim($_POST['AreaType']);
    $desc   = trim($_POST['AreaDescription']);
    $cap    = intval($_POST['Capacity']);
    $loc    = trim($_POST['LocationDesc']);
    $status = trim($_POST['AreaStatus']);

    // Generate new ParkingAreaID safely
    $res = $conn->query("
        SELECT MAX(CAST(SUBSTRING(ParkingAreaID,3) AS UNSIGNED)) AS maxID 
        FROM parking_area
    ");
    $next = ($res->fetch_assoc()['maxID'] ?? 0) + 1;
    $id   = 'PA' . str_pad($next, 4, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("
        INSERT INTO parking_area
        (ParkingAreaID, AreaCode, AreaName, AreaType, AreaDescription, Capacity, LocationDesc, AreaStatus)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sssssiss",
        $id,
        $code,
        $name,
        $type,
        $desc,
        $cap,
        $loc,
        $status
    );
    $stmt->execute();

    header("Location: manage_parking_area.php");
    exit;
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Add Parking Area</title>
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

            <header class="header">➕ Add Parking Area</header>

            <div class="box">
                <form method="post" class="form-grid">

                    <label>Area Code</label>
                    <input name="AreaCode" placeholder="A1" required>

                    <label>Area Name</label>
                    <input name="AreaName" placeholder="Main Block" required>

                    <label>Area Type</label>
                    <select name="AreaType">
                        <option value="Open">Open</option>
                        <option value="Covered">Covered</option>
                    </select>

                    <label>Description</label>
                    <textarea name="AreaDescription" rows="3"></textarea>

                    <label>Capacity</label>
                    <input type="number" name="Capacity" required>

                    <label>Location</label>
                    <input name="LocationDesc" placeholder="Near Gate A">

                    <label>Status</label>
                    <select name="AreaStatus">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>

                    <div class="form-actions">
                        <button class="btn-success" type="submit">Save Area</button>
                        <br><br>
                        <a class="btn-danger" href="manage_parking_area.php">Cancel</a>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <script>
        window.addEventListener("pageshow", function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

</body>

</html>