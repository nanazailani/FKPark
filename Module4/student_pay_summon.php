<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header("Location: ../Module1/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Invalid summon ID.";
    exit();
}

$summonID = $_GET['id'];

// Fetch summon details
$sql = "SELECT s.*, v.PlateNumber, vt.ViolationName, vt.DemeritPoints 
        FROM Summon s
        JOIN Vehicle v ON s.VehicleID = v.VehicleID
        JOIN ViolationType vt ON s.ViolationTypeID = vt.ViolationTypeID
        WHERE s.SummonID = '$summonID'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "Summon not found.";
    exit();
}

// If already paid, stop here
if (strtolower($data['SummonStatus']) === 'paid') {
    echo "<script>window.location='student_demerit_points.php?paid=1';</script>";
    exit();
}

// Handle payment confirmation
if (isset($_POST['confirmPayment'])) {
    $sqlPay = "UPDATE Summon SET SummonStatus='Paid' WHERE SummonID='$summonID'";
    mysqli_query($conn, $sqlPay);

    $paymentSuccess = true;
    $data['SummonStatus'] = 'Paid';
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Pay Summon</title>
    <link rel="stylesheet" href="../templates/student_style.css">
    <style>
        .pay-box {
            background: #ffffff;
            border-left: 8px solid #6BA8FF;
            border-radius: 16px;
            padding: 25px;
            width: 85%;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: 0.2s ease-in-out;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            background: #DCEAFF;
            padding: 10px 15px;
            border-radius: 10px;
            color: #0A3D62;
            margin-bottom: 15px;
        }

        .confirm-btn {
            margin-top: 20px;
            padding: 10px 18px;
            background: #1B73E8;
            color: white;
            font-weight: 700;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }

        .confirm-btn:hover {
            background: #0F5DD1;
        }

        .detail-value {
            font-size: 18px;
        }

        .detail-row {
            margin-bottom: 12px;
        }

        .success-message {
            background: #E6FFE6;
            border-left: 8px solid #38B000;
            padding: 15px;
            border-radius: 10px;
            color: #166400;
            font-weight: 700;
            margin-bottom: 20px;
        }

        @keyframes fadeInOut {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }

            10% {
                opacity: 1;
                transform: translateY(0);
            }

            90% {
                opacity: 1;
                transform: translateY(0);
            }

            100% {
                opacity: 0;
                transform: translateY(-10px);
            }
        }
    </style>
</head>

<body>
    <?php include '../templates/student_sidebar.php'; ?>
    <div class="main-content">

        <?php if (!empty($paymentSuccess)): ?>
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    document.getElementById('paymentPopup').style.display = "flex";
                });
            </script>
        <?php endif; ?>

        <!-- PAYMENT POPUP BACKDROP -->
        <div id="paymentPopup"
            style="
            display:none;
            position:fixed;
            top:0; left:0;
            width:100%; height:100%;
            background:rgba(0,0,0,0.4);
            z-index:9999;
            justify-content:center;
            align-items:center;
            animation: fadeIn 0.3s ease;">

            <!-- POPUP BOX -->
            <div style="
                background:#E6FFE6;
                padding:25px;
                width:350px;
                border-radius:18px;
                border:3px solid #38B000;
                box-shadow:0 4px 12px rgba(56,176,0,0.4);
                text-align:center;
                animation: scaleIn 0.3s ease;">

                <h3 style="color:#166400; margin-bottom:15px;">
                    ✔ Payment Successful
                </h3>

                <button id="closePopup"
                    style="
                    margin-top:20px;
                    padding:10px 20px;
                    background:#38B000;
                    border:none;
                    border-radius:10px;
                    color:white;
                    font-weight:bold;
                    cursor:pointer;">
                    Close
                </button>
            </div>
        </div>

        <a href="student_demerit_points.php"
            style="
                display:inline-block;
                margin-bottom:15px;
                text-decoration:none;
                color:#1B73E8;
                font-weight:700;
                font-size:16px;">
            ← Back to My Demerit Points
        </a>
        <div class="pay-box">

            <div class="section-title">Summon Payment Confirmation</div>

            <div class="detail-row">
                <span class="detail-label">Summon ID:</span>
                <span class="detail-value"><?= $data['SummonID']; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Plate Number:</span>
                <span class="detail-value"><?= $data['PlateNumber']; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Violation:</span>
                <span class="detail-value"><?= $data['ViolationName']; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Demerit Points:</span>
                <span class="detail-value"><strong>+<?= $data['DemeritPoints']; ?></strong></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Current Status:</span>
                <span class="detail-value" style="color:red; font-weight:700;">Unpaid</span>
            </div>

            <form method="POST">
                <button type="submit" name="confirmPayment" class="confirm-btn" style="font-size:18px; padding:12px 25px;">Confirm Payment</button>
            </form>

        </div>

    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const urlParams = new URLSearchParams(window.location.search);
            // No auto redirect or param check needed anymore

            document.getElementById("closePopup").onclick = () => {
                document.getElementById('paymentPopup').style.display = "none";
            };
        });
    </script>
</body>

</html>