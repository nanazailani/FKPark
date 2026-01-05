<?php
// Enable error reporting supaya senang debug masa development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session untuk simpan info login (UserRole, UserID)
session_start();
// Disable cache supaya page sentiasa load data terbaru
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
// Include config.php untuk sambung ke database
require_once '../config.php';

// Security check: hanya Security Staff boleh akses page ini
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../index.php");
    exit();
}

// Ambil SummonID dari URL (GET)
$summonID = (int)($_GET['id'] ?? 0);

/*
Query untuk ambil maklumat lengkap saman:
- Summon (maklumat saman)
- Vehicle (plate number)
- ViolationType (nama kesalahan & mata)
- User (maklumat pelajar)
- SummonQRCode (QR code)
- Subquery untuk kira total mata demerit pelajar
- Subquery untuk dapatkan status enforcement yang masih aktif
*/
$sql = "
SELECT 
    s.*, 
    v.PlateNumber, 
    vt.ViolationName, 
    vt.ViolationPoints,

    u.UserName AS StudentName,
    u.UserID AS StudentID,

    COALESCE((
        SELECT SUM(vt2.ViolationPoints)
        FROM Summon s2
        JOIN ViolationType vt2 ON s2.ViolationTypeID = vt2.ViolationTypeID
        JOIN Vehicle v2 ON s2.VehicleID = v2.VehicleID
        WHERE v2.UserID = u.UserID
    ), 0) AS TotalDemeritPoints,

    COALESCE((
        SELECT p.PunishmentType
        FROM PunishmentDuration p
        WHERE p.UserID = u.UserID
          AND p.Status = 'Active'
          AND CURDATE() BETWEEN p.StartDate AND p.EndDate
        ORDER BY p.StartDate DESC
        LIMIT 1
    ), 'None') AS EnforcementStatus,

    sq.QRCodeData, 
    sq.QRCodeID
FROM Summon s
JOIN Vehicle v ON s.VehicleID = v.VehicleID
JOIN ViolationType vt ON s.ViolationTypeID = vt.ViolationTypeID
JOIN User u ON v.UserID = u.UserID
LEFT JOIN SummonQRCode sq ON s.SummonID = sq.SummonID
WHERE s.SummonID = '$summonID'
";

// Execute query dan ambil data saman
$result = mysqli_query($conn, $sql);
$summon = mysqli_fetch_assoc($result);

// Kalau summon tak jumpa, redirect balik ke dashboard
if (!$summon) {
    header("Location: security_dashboard.php");
    exit();
}

// Kira mata demerit sebelum saman ini dikeluarkan
$previousPoints = max(0, $summon['TotalDemeritPoints'] - $summon['ViolationPoints']);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Summon Created</title>
    <link rel="stylesheet" href="../templates/security_style.css">

    <style>
        .success-box {
            background: #FFFAE1;
            padding: 20px;
            border-left: 8px solid #F4C542;
            border-radius: 15px;
            font-size: 16px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .success-box strong {
            color: #7A5A00;
        }

        .qr-box {
            background: #FFF9D7;
            padding: 25px;
            text-align: center;
            border-radius: 15px;
            border: 1px solid #FFE08C;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
        }

        .fake-qr {
            width: 200px;
            height: 200px;
            background: white;
            border: 2px solid #EEE;
            margin: auto;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 12px;
            font-size: 45px;
            color: #555;
        }

        .btn-yellow {
            background: #FFC93C;
            color: #5A4B00;
            padding: 12px 20px;
            border-radius: 10px;
            display: inline-block;
            text-decoration: none;
            font-weight: 700;
            transition: 0.2s;
            margin-top: 15px;
        }

        .btn-yellow:hover {
            background: #FFBB22;
            transform: scale(1.05);
        }

        .detail-row {
            display: flex;
            flex-direction: row;
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

        .section-title {
            background: #FFF4C7;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 20px;
            font-weight: 700;
            color: #5A4B00;
            margin: 25px 0 15px;
        }

        .divider {
            width: 100%;
            height: 1px;
            background: #E8D9A8;
            margin: 20px 0;
        }

        /* --- PRINT ONLY BOX --- */
        @media print {
            body * {
                visibility: hidden;
            }

            .box,
            .box * {
                visibility: visible;
            }

            .box {
                position: absolute;
                top: 20px;
                left: 20px;
                width: 90%;
            }
        }
    </style>
</head>

<body>

    <?php include '../templates/security_sidebar.php'; ?>

    <div class="main-content">

        <div class="header">🎉 Summon Created Successfully</div>

        <!-- Kotak success: paparkan status saman berjaya dicipta -->
        <div class="success-box">
            ✓ Summon <strong><?= $summon['SummonID']; ?></strong> has been successfully created!<br>
            Demerit points updated: <strong><?= $previousPoints; ?> → <?= $summon['TotalDemeritPoints']; ?></strong><br>
            Enforcement status: <strong><?= $summon['EnforcementStatus'] ?: 'None'; ?></strong>
        </div>

        <div class="box">
            <!-- Section: Maklumat penuh saman -->
            <div class="section-title">Summon Details</div>

            <div class="detail-row"><span class="detail-label">Summon ID:</span>
                <span class="detail-value"><?= $summon['SummonID']; ?></span>
            </div>

            <div class="detail-row"><span class="detail-label">Student:</span>
                <span class="detail-value"><?= $summon['StudentName']; ?> (<?= $summon['StudentID']; ?>)</span>
            </div>

            <div class="detail-row"><span class="detail-label">Plate Number:</span>
                <span class="detail-value"><?= $summon['PlateNumber']; ?></span>
            </div>

            <div class="detail-row"><span class="detail-label">Violation:</span>
                <span class="detail-value"><?= $summon['ViolationName']; ?></span>
            </div>

            <div class="detail-row"><span class="detail-label">Demerit Points:</span>
                <span class="detail-value"><?= $summon['ViolationPoints']; ?></span>
            </div>

            <div class="detail-row"><span class="detail-label">Date & Time:</span>
                <span class="detail-value">
                    <?= date('d F Y, g:i A', strtotime($summon['SummonDate'] . ' ' . $summon['SummonTime'])); ?>
                </span>
            </div>

            <div class="detail-row"><span class="detail-label">Location:</span>
                <span class="detail-value"><?= $summon['Location']; ?></span>
            </div>

            <div class="divider"></div>

            <!-- Section: QR code untuk view saman -->
            <div class="section-title">Summon QR Code</div>
            <div class="qr-box">
                <?php if (!empty($summon['QRCodeData'])): ?>
                    <img src="<?= htmlspecialchars('qrcodes/' . basename($summon['QRCodeData'])); ?>"
                        style="width:200px;height:200px;border-radius:12px;border:2px solid #EEE;">
                <?php else: ?>
                    <p>No QR code available.</p>
                <?php endif; ?>
                <p style="color:#7A5A00;">Scan to view summon in system</p>
                <!-- Button untuk print QR code sahaja -->
                <button type="button" onclick="window.print()" class="btn-yellow">
                    Print QR Code
                </button>
            </div>
        </div>

        <!-- Navigation buttons: create new summon & back to dashboard -->
        <div style="margin-top:20px;">
            <a href="security_create_summon.php" class="btn-yellow">Create New Summon</a>
            <a href="security_dashboard.php" class="btn-yellow" style="background:#FFE28A;">Back to Dashboard</a>
        </div>

    </div>
    <script>
        // Fix issue bila tekan back button (force reload page bila cached)
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