<?php
// ================= SESSION & SECURITY =================
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once '../config.php';

// Only Student can access
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header("Location: ../Module1/login.php");
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

// ================= FETCH STUDENT VEHICLES =================
$vehicles = mysqli_query($conn, "
    SELECT * FROM Vehicle
    WHERE UserID = '$studentID'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Vehicles</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Student layout -->
    <link rel="stylesheet" href="../templates/student_style.css">

    <!-- Blue theme -->
    <style>
        .vehicle-card {
            background: #ffffff;
            border-left: 8px solid #5B9BFF;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .status-badge {
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 14px;
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

        .btn-cancel {
            background: #C40000;
            color: white;
            font-weight: 700;
            border-radius: 10px;
            padding: 6px 14px;
            border: none;
        }

        .btn-cancel:hover {
            background: #9E0000;
        }
    </style>
</head>

<body class="bg-light">

<?php include '../templates/student_sidebar.php'; ?>

<div class="main-content">

    <div class="container mt-4">

        <div class="header mb-4">📄 My Registered Vehicles</div>

        <?php if (mysqli_num_rows($vehicles) == 0): ?>
            <div class="alert alert-info">
                You have not registered any vehicles yet.
            </div>
        <?php else: ?>

            <?php while ($v = mysqli_fetch_assoc($vehicles)): ?>
                <div class="card vehicle-card mb-3">
                    <div class="card-body">

                        <p><strong>Plate Number:</strong> <?= htmlspecialchars($v['PlateNumber']) ?></p>
                        <p><strong>Type:</strong> <?= htmlspecialchars($v['VehicleType']) ?></p>

                        <p>
                            <strong>Status:</strong>
                            <?php if ($v['ApprovalStatus'] == "Pending"): ?>
                                <span class="status-badge pending">Pending Approval</span>
                            <?php elseif ($v['ApprovalStatus'] == "Approved"): ?>
                                <span class="status-badge approved">Approved</span>
                            <?php else: ?>
                                <span class="status-badge rejected">Rejected</span>
                            <?php endif; ?>
                        </p>

                        <?php if (!empty($v['VehicleGrant'])): ?>
                            <p>
                                <strong>Vehicle Grant:</strong>
                                <a href="<?= $v['VehicleGrant'] ?>" target="_blank" class="fw-bold">
                                    [View]
                                </a>
                            </p>
                        <?php endif; ?>

                        <?php if ($v['ApprovalStatus'] == "Pending"): ?>
                            <form method="POST"
                                  onsubmit="return confirm('Cancel this vehicle application?');">
                                <input type="hidden" name="vehicleID" value="<?= $v['VehicleID'] ?>">
                                <button type="submit" name="delete_vehicle" class="btn btn-cancel">
                                    ❌ Cancel Application
                                </button>
                            </form>
                        <?php endif; ?>

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
