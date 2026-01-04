<?php
// start session
session_start();

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// connect database
require_once '../config.php';

// check if security staff
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../Module1/login.php");
    exit();
}

// ================= HANDLE APPROVE / REJECT =================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $vehicleID = mysqli_real_escape_string($conn, $_POST['vehicleID']);
    $staffID   = $_SESSION['UserID'];

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

// ================= GET ALL PENDING VEHICLES =================
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Vehicles</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Security layout -->
    <link rel="stylesheet" href="../templates/security_style.css">

    <style>
        /* OUTER CARD – light yellow */
        .vehicle-outer {
            background: #ffffff;
            border-left: 8px solid #FFE28A;
            border-radius: 20px;
            padding: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        /* INNER CARD – white */
        .vehicle-inner {
            background: #ffffff;
            border-radius: 16px;
            padding: 10px;
        }

        .vehicle-inner p {
            margin: 6px 0;
            color: #6A3C00;
            font-size: 15px;
            font-weight: 500;
        }

        .btn-approve {
            background: #4CAF50;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            margin-right: 10px;
        }

        .btn-reject {
            background: #D32F2F;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
        }

        .no-data {
            background: #FFF3D0;
            border-radius: 20px;
            padding: 20px;
            font-weight: 600;
            color: #6A3C00;
        }
    </style>
</head>

<body class="bg-light">

<?php include "../templates/security_sidebar.php"; ?>

<div class="main-content">
    <div class="container mt-4">

        <div class="header mb-4">🚗 Approve Vehicle Registrations</div>

        <?php if (mysqli_num_rows($vehicles) == 0): ?>
            <div class="no-data">No pending vehicle registrations.</div>
        <?php else: ?>

            <?php while ($v = mysqli_fetch_assoc($vehicles)): ?>

                <!-- YELLOW BACKGROUND -->
                <div class="vehicle-outer">

                    <!-- WHITE INNER CARD -->
                    <div class="vehicle-inner">

                        <p><strong>Plate Number:</strong> <?= $v['PlateNumber']; ?></p>
                        <p><strong>Owner:</strong> <?= $v['UserName']; ?> (<?= $v['UserEmail']; ?>)</p>
                        <p><strong>Type:</strong> <?= $v['VehicleType']; ?></p>

                        <p>
                            <strong>Vehicle Grant:</strong>
                            <a href="<?= $v['VehicleGrant']; ?>" 
                               target="_blank" 
                               style="color:#FF7A00; font-weight:700;">
                                View 📄
                            </a>
                        </p>

                        <form method="POST" class="mt-3">
                            <input type="hidden" name="vehicleID" value="<?= $v['VehicleID']; ?>">

                            <button name="approve" class="btn-approve">
                                Approve ✔
                            </button>

                            <button name="reject" class="btn-reject">
                                Reject ✖
                            </button>
                        </form>

                    </div>
                </div>

            <?php endwhile; ?>

        <?php endif; ?>
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
