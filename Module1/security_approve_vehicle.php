<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Allow only Security Staff
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../index.php");
    exit();
}

// ======================= HANDLE APPROVE / REJECT =========================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $vehicleID = mysqli_real_escape_string($conn, $_POST['vehicleID']);
    $staffID   = $_SESSION['UserID']; // Security Staff ID

    if (isset($_POST['approve'])) {
        $status = "Approved";
    } elseif (isset($_POST['reject'])) {
        $status = "Rejected";
    } else {
        exit();
    }

    mysqli_query($conn, "
        UPDATE vehicle
        SET ApprovalStatus = '$status',
            ApprovedBy = '$staffID'
        WHERE VehicleID = '$vehicleID'
    ");

    echo "<script>
        alert('Vehicle status updated!');
        window.location='security_approve_vehicle.php';
    </script>";
    exit();
}


// ======================= GET ALL PENDING VEHICLES =========================
$query = "
    SELECT V.*, U.UserName, U.UserEmail
    FROM Vehicle V
    JOIN Student S ON V.UserID = S.UserID
    JOIN User U ON S.UserID = U.UserID
    WHERE V.ApprovalStatus = 'Pending'
";

$vehicles = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Approve Vehicles</title>
    <link rel="stylesheet" href="../templates/security_style.css">

    <style>
        .vehicle-card {
            background: #FFFFFF;
            padding: 20px;
            border-radius: 15px;
            border-left: 8px solid #FFD972;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .vehicle-card p {
            margin: 5px 0;
            color: #6A3C00;
            font-size: 15px;
            font-weight: 500;
        }

        .btn-approve {
            background: #4CAF50;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-reject {
            background: #D32F2F;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .no-data {
            padding: 20px;
            background: #FFF3D0;
            border-left: 8px solid #FFD972;
            border-radius: 12px;
            font-weight: 600;
            color: #6A3C00;
        }
    </style>
</head>

<body>

    <?php include "../templates/security_sidebar.php"; ?>

    <div class="main-content">
        <div class="header">🚗 Approve Vehicle Registrations</div>

        <?php if (mysqli_num_rows($vehicles) == 0): ?>
            <div class="no-data">No pending vehicle registrations.</div>
        <?php else: ?>

            <?php while ($v = mysqli_fetch_assoc($vehicles)): ?>
                <div class="vehicle-card">

                    <p><strong>Plate Number:</strong> <?= $v['PlateNumber']; ?></p>
                    <p><strong>Owner:</strong> <?= $v['UserName']; ?> (<?= $v['UserEmail']; ?>)</p>
                    <p><strong>Type:</strong> <?= $v['VehicleType']; ?></p>

                    <p>
                        <strong>Vehicle Grant:</strong>
                        <a href="<?= $v['VehicleGrant']; ?>" target="_blank" style="color:#FF7A00; font-weight:600;">
                            View 📄
                        </a>
                    </p>

                    <form method="POST" style="margin-top:10px;">
                        <input type="hidden" name="vehicleID" value="<?= $v['VehicleID']; ?>">

                        <button name="approve" class="btn-approve">Approve ✔</button>
                        <button name="reject" class="btn-reject">Reject ✖</button>
                    </form>

                </div>
            <?php endwhile; ?>

        <?php endif; ?>
    </div>
    <script>
        // If the page was loaded from cache (e.g., user pressed Back)
        window.addEventListener("pageshow", function(event) {
            if (event.persisted) {
                // Force a full reload
                window.location.reload();
            }
        });
    </script>
</body>

</html>