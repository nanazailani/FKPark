<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header("Location: ../index.php");
    exit();
}

$studentID = $_SESSION['UserID'];

// ================= CANCEL PENDING VEHICLE =================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_vehicle'])) {

    $vehicleID = mysqli_real_escape_string($conn, $_POST['vehicleID']);

    $check = mysqli_query($conn, "
        SELECT VehicleID FROM Vehicle
        WHERE VehicleID = '$vehicleID'
        AND UserID = '$studentID'
        AND ApprovalStatus = 'Pending'
    ");

    if (mysqli_num_rows($check) == 1) {
        mysqli_query($conn, "
            DELETE FROM Vehicle
            WHERE VehicleID = '$vehicleID'
        ");
    }

    header("Location: student_view_vehicle.php");
    exit();
}

// Fetch all vehicles owned by the student
$vehicles = mysqli_query($conn, "
    SELECT * FROM Vehicle WHERE UserID = '$studentID'
");
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Vehicles</title>
    <link rel="stylesheet" href="../templates/student_style.css">

    <style>
        .vehicle-box {
            background: #fff;
            padding: 20px;
            border-radius: 20px;
            border-left: 8px solid #5B9BFF;
            width: 80%;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .vehicle-item {
            padding: 15px;
            border-bottom: 1px solid #DCEBFF;
            margin-bottom: 10px;
        }

        .status {
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 10px;
            display: inline-block;
        }

        .pending {
            background: #FFF1C2;
            color: #A66A00;
            border: 1px solid #FFD262;
        }

        .approved {
            background: #D1FFD8;
            color: #1B6B2D;
            border: 1px solid #71D483;
        }

        .rejected {
            background: #FFD6D6;
            color: #B30000;
            border: 1px solid #FF8A8A;
        }

        .cancel {
            background: #FFD6D6;
            color: #B30000;
            border: 1px solid #FF8A8A
        }

        .cancel:hover {
            background: red;
            transform: scale(1.03);
        }

        img.grant-img {
            width: 200px;
            border-radius: 10px;
            margin-top: 10px;
            border: 2px solid #5B9BFF;
        }

        .no-vehicle {
            color: #003A75;
            font-size: 16px;
            padding: 20px;
        }
    </style>
</head>

<body>

    <?php include '../templates/student_sidebar.php'; ?>

    <div class="main-content">
        <div class="header">📄 My Registered Vehicles</div>

        <div class="vehicle-box">

            <?php if (mysqli_num_rows($vehicles) == 0): ?>
                <p class="no-vehicle">You have not registered any vehicles yet.</p>
            <?php else: ?>

                <?php while ($v = mysqli_fetch_assoc($vehicles)): ?>
                    <div class="vehicle-item">
                        <p><strong>Plate Number:</strong> <?= $v['PlateNumber'] ?></p>
                        <p><strong>Type:</strong> <?= $v['VehicleType'] ?></p>

                        <p><strong>Status:</strong>
                            <?php if ($v['ApprovalStatus'] == "Pending"): ?>
                                <span class="status pending">Pending Approval</span>

                            <?php elseif ($v['ApprovalStatus'] == "Approved"): ?>
                                <span class="status approved">Approved</span>

                            <?php elseif ($v['ApprovalStatus'] == "Rejected"): ?>
                                <span class="status rejected">Rejected</span>

                            <?php endif; ?>
                        </p>

                        <?php if (!empty($v['VehicleGrant'])): ?>
                            <p><strong>Vehicle Grant:</strong>
                                <a href="<?= $v['VehicleGrant'] ?>"
                                    target="_blank"
                                    style="color:#1B98E0; font-weight:700; text-decoration:none;">
                                    [View]
                                </a>
                            </p>
                        <?php endif; ?>

                        <?php if ($v['ApprovalStatus'] == "Pending"): ?>
                            <form method="POST"
                                onsubmit="return confirm('Cancel this vehicle application?');">
                                <input type="hidden" name="vehicleID" value="<?= $v['VehicleID'] ?>">
                                <button class="status cancel" type="submit" name="delete_vehicle" class="btn btn-cancel">
                                    ❌ Cancel Application
                                </button>
                            </form>
                        <?php endif; ?>


                    </div>
                <?php endwhile; ?>

            <?php endif; ?>

        </div>

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