<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Missing Summon ID";
    exit();
}

$summonID = $_GET['id'];

// Handle Update Status
if (isset($_POST['updateStatus'])) {
    $newStatus = $_POST['SummonStatus'];
    $notes = mysqli_real_escape_string($conn, $_POST['Notes']);

    $sqlUpdate = "
        UPDATE Summon 
        SET SummonStatus = '$newStatus', Notes = '$notes'
        WHERE SummonID = '$summonID'
    ";
    mysqli_query($conn, $sqlUpdate);

    echo "<script>alert('Summon updated successfully!'); 
          window.location='security_summon_view.php?id=$summonID';</script>";
    exit();
}

// Fetch Summon Details
$sql = "
    SELECT 
        S.*, 
        U.UserName, 
        U.UserID,
        V.PlateNumber,
        VT.ViolationName,
        D.DemeritPoints,
        VT.Description AS ViolationDescription
    FROM Summon S
    LEFT JOIN Vehicle V ON S.VehicleID = V.VehicleID
    LEFT JOIN User U ON V.UserID = U.UserID
    LEFT JOIN ViolationType VT ON S.ViolationTypeID = VT.ViolationTypeID
    LEFT JOIN Demerit D ON D.SummonID = S.SummonID
    WHERE S.SummonID = '$summonID'
";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 0) {
    echo "Summon not found.";

    echo "<script>alert('Summon updated successfully!'); 
    window.location='security_summon_view.php?id=$summonID';</script>";
    exit();
}

$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Summon</title>
    <link rel="stylesheet" href="../templates/security_style.css">

    <style>
        .view-card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-left: 10px solid #FFE08C;
            width: 90%;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #5A4B00;
            background: #FFF4C7;
            padding: 10px 15px;
            border-radius: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin: 8px 0;
        }

        .detail-label {
            width: 180px;
            font-weight: 700;
            color: #5A4B00;
        }

        .detail-value {
            font-weight: 500;
            color: #5A4B00;
        }


        .status-badge {
            padding: 6px 14px;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            text-transform: capitalize;
        }

        /* RED - Unpaid */
        .status-badge.unpaid {
            background: #FF6B6B;
        }

        /* GREEN - Paid */
        .status-badge.paid {
            background: #4CAF50;
        }

        /* YELLOW - Cancelled */
        .status-badge.cancelled {
            background: #FFC93C;
            color: #5A4B00;
        }

        /* ORANGE - Rejected */
        .status-badge.rejected {
            background: #FF9800;
        }


        .divider {
            height: 1px;
            width: 100%;
            background: #F1DFA6;
            margin: 15px 0;
        }

        .evidence-box {
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }

        .evidence-box img {
            max-width: 300px;
            border-radius: 12px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <?php include '../templates/security_sidebar.php'; ?>

    <div class="main-content">
        <div class="header">📄 Summon Details</div>

        <div class="view-card">

            <!-- === Student & Vehicle Section === -->
            <div class="section-title">Student & Vehicle Information</div>

            <div class="detail-row">
                <span class="detail-label">Student Name:</span>
                <span class="detail-value"><?= $data['UserName']; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Student ID:</span>
                <span class="detail-value"><?= $data['UserID']; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Plate Number:</span>
                <span class="detail-value"><?= $data['PlateNumber']; ?></span>
            </div>


            <div class="divider"></div>

            <!-- === Violation Section === -->
            <div class="section-title">Violation Details</div>

            <div class="detail-row">
                <span class="detail-label">Violation:</span>
                <span class="detail-value"><?= $data['ViolationName']; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Demerit Points:</span>
                <span class="detail-value"><?= $data['DemeritPoints']; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Description:</span>
                <span class="detail-value"><?= $data['ViolationDescription']; ?></span>
            </div>

            <div class="divider"></div>

            <!-- === Summon Info Section === -->
            <div class="section-title">Summon Information</div>

            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value"><?= $data['SummonDate']; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Time:</span>
                <span class="detail-value"><?= $data['SummonTime']; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Location:</span>
                <span class="detail-value"><?= $data['Location']; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value">
                    <span class="status-badge <?= strtolower($data['SummonStatus']); ?>">
                        <?= $data['SummonStatus']; ?>
                    </span>
                </span>
            </div>


            <div class="divider"></div>

            <!-- === Evidence Section === -->
            <div class="section-title">Evidence</div>

            <div class="evidence-box">
                <?php
                $evidence = $data['Evidence'];

                // auto-fix older "../uploads/" paths
                if (str_starts_with($evidence, "../uploads/")) {
                    $filename = basename($evidence);
                    $evidence = "http://localhost/WebEng/FKPark/uploads/" . $filename;
                }
                ?>

                <?php if (!empty($evidence)): ?>
                    <?php if (preg_match("/\.(jpg|jpeg|png|gif)$/i", $evidence)): ?>
                        <img src="<?= $evidence; ?>" alt="Evidence Image">
                    <?php else: ?>
                        <a href="<?= $evidence; ?>" target="_blank">
                            <b>View Attached File</b>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <p>No evidence uploaded.</p>
                <?php endif; ?>
            </div>

            <div class="divider"></div>



            <div style="margin-top: 25px; text-align:center;">
                <a href="security_summon_list.php"
                    style="font-size:20px; font-weight:700; color:#4A3F00; text-decoration:none;">
                    ← Back to Summon List
                </a>
            </div>
        </div>
        <script>
            //pageshow - event bila page show. e.g - tekan background
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