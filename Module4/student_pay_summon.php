<?php
// Enable error reporting supaya senang debug masa development
error_reporting(E_ALL);
// Start session untuk simpan info login student
ini_set('display_errors', 1);
session_start();
// Disable cache supaya page sentiasa load data terbaru
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
// Include config.php untuk sambung ke database
require_once '../config.php';

// Security check: pastikan hanya Student boleh akses page payment
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {

    // Simpan page ini supaya lepas login boleh patah balik
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    header("Location: ../Module1/login.php");
    exit();
}

// Check sama ada SummonID dihantar melalui URL (match QR & view page)
$summonID = (int)($_GET['summon_id'] ?? 0);

if ($summonID <= 0) {
    echo "Invalid summon ID.";
    exit();
}

/*
Query untuk ambil maklumat saman:
- Summon (maklumat saman)
- Vehicle (plate number)
- ViolationType (jenis kesalahan & mata demerit)
*/
// Execute query dan ambil data saman
$sql = "SELECT 
            s.*, 
            v.PlateNumber, 
            vt.ViolationName, 
            d.DemeritPoints
        FROM Summon s
        JOIN Vehicle v ON s.VehicleID = v.VehicleID
        JOIN ViolationType vt ON s.ViolationTypeID = vt.ViolationTypeID
        LEFT JOIN Demerit d ON d.SummonID = s.SummonID
        WHERE s.SummonID = '$summonID'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "Summon not found.";
    exit();
}

// Kalau saman sudah dibayar, terus redirect balik ke page demerit
if (strtolower($data['SummonStatus']) === 'paid') { //strtolower = string to lower
    echo "<script>window.location='student_demerit_points.php?paid=1';</script>";
    exit();
}

// Handle confirm payment bila student tekan button
if (isset($_POST['confirmPayment'])) {
    // Update status saman kepada 'Paid'
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
                // Papar popup bila payment berjaya by using addEventListener function
                document.addEventListener("DOMContentLoaded", () => {
                    document.getElementById('paymentPopup').style.display = "flex";
                });
            </script>
        <?php endif; ?>

        <!-- Popup hijau: paparan payment berjaya -->
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

        <!-- Link untuk kembali ke page demerit points -->
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
        <!-- Box utama: paparkan maklumat saman untuk pengesahan -->
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

            <!-- Papar mata demerit untuk saman ini -->
            <div class="detail-row">
                <span class="detail-label">Demerit Points:</span>
                <span class="detail-value"><strong>+<?= $data['DemeritPoints']; ?></strong></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Current Status:</span>
                <span class="detail-value" style="color:red; font-weight:700;">Unpaid</span>
            </div>

            <!-- Button untuk sahkan pembayaran saman -->
            <form method="POST">
                <button type="submit" name="confirmPayment" class="confirm-btn" style="font-size:18px; padding:12px 25px;">Confirm Payment</button>
            </form>

        </div>

    </div>
    <script>
        // Papar popup bila payment berjaya
        document.addEventListener("DOMContentLoaded", () => {
            const urlParams = new URLSearchParams(window.location.search); //add parameter to url
            // No auto redirect or param check needed anymore
            document.getElementById("closePopup").onclick = () => {
                document.getElementById('paymentPopup').style.display = "none";
            };
        });
    </script>
    <script>
        // Fix issue back button (reload page bila cached)
        window.addEventListener("pageshow", function(event) {
            //true kalau the page is cached 
            if (event.persisted) {
                //page reload
                window.location.reload();
            }
        });
    </script>
</body>

</html>