<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header("Location: ../Module1/login.php");
    exit();
}

$userID = $_SESSION['UserID'];

// Count student vehicles
$totalVehicles = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM Vehicle WHERE StudentID = '$userID'
"))['total'];

// Count approved vehicles
$approved = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM Vehicle WHERE StudentID = '$userID' AND ApprovalStatus = 'Approved'
"))['total'];

// Count pending vehicles
$pending = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM Vehicle WHERE StudentID = '$userID' AND ApprovalStatus = 'Pending'
"))['total'];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../templates/student_style.css">

    <style>
        /* BLUE THEME */
        .stat-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-box {
            flex: 1;
            min-width: 180px;
            background: #DCEBFF;
            padding: 18px;
            border-radius: 18px;
            border-left: 8px solid #5B9BFF;
            text-align: center;
            color: #003A75;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .stat-box h1 {
            margin: 0;
            font-size: 32px;
            color: #1D6BFF;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .info-box {
            background: #E7F1FF;
            border-left: 8px solid #5B9BFF;
            padding: 18px;
            border-radius: 15px;
            margin-bottom: 20px;
            color: #003A75;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <?php include '../templates/student_sidebar.php'; ?>

    <div class="main-content">
        <div class="header">🧑‍🎓 Student Dashboard</div>

        <div class="info-box">
            Welcome back, <strong><?= $_SESSION['UserName']; ?></strong>!  
            Manage your vehicle registration and check approval status here.
        </div>

        <!-- STATISTICS -->
        <div class="stat-grid">

            <div class="stat-box">
                <h1><?= $totalVehicles ?></h1>
                Total Vehicles
            </div>

            <div class="stat-box">
                <h1><?= $approved ?></h1>
                Approved
            </div>

            <div class="stat-box">
                <h1><?= $pending ?></h1>
                Pending Approval
            </div>

        </div>

        <div class="info-box">
            📌 Tip: Make sure your vehicle grant upload is clear to get faster approval.
        </div>

    </div>
<script>
    // If the page was loaded from cache (e.g., user pressed Back)
    window.addEventListener("pageshow", function (event) {
        if (event.persisted) {
            // Force a full reload
            window.location.reload();
        }
    });
</script>
</body>

</html>
