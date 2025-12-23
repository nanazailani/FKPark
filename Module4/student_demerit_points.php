<?php
// Enable error reporting supaya senang debug masa development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session untuk simpan info login student
session_start();
// Disable cache supaya data sentiasa latest bila refresh / back button
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
// Include config.php untuk sambung ke database
require_once '../config.php';

// Security check: hanya Student dibenarkan akses page ini
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header('Location: ../Module1/login.php');
    exit();
}

$userID = $_SESSION['UserID'];

/* --------------------------------
   FETCH STUDENT RECORD
----------------------------------*/
// Ambil maklumat pelajar berdasarkan UserID dari session
$sql = "SELECT * FROM User WHERE UserID = '$userID'";
$result = mysqli_query($conn, $sql);
$student = mysqli_fetch_assoc($result);

/* --------------------------------
   TOTAL DEMERIT POINTS
----------------------------------*/
/*
Kira jumlah keseluruhan mata demerit pelajar.
JOIN digunakan untuk gabungkan:
- Summon (rekod saman)
- ViolationType (mata kesalahan)
- Vehicle (kenderaan milik pelajar)
*/
$sqlPoints = "
    SELECT SUM(vt.ViolationPoints) AS TotalPoints
    FROM Summon s
    JOIN ViolationType vt ON s.ViolationTypeID = vt.ViolationTypeID
    JOIN Vehicle v ON s.VehicleID = v.VehicleID
    WHERE v.UserID = '$userID'
";
$resPoints = mysqli_query($conn, $sqlPoints);
$rowPoints = mysqli_fetch_assoc($resPoints);
$totalPoints = $rowPoints['TotalPoints'] ?? 0;

/* --------------------------------
   SUMMON FILTERING
----------------------------------*/
// Filter saman berdasarkan status (all / paid / unpaid)
$filter = $_GET['filter'] ?? 'all';
$filterQuery = "";

if ($filter === 'paid') {
    $filterQuery = "AND s.SummonStatus = 'Paid'";
} elseif ($filter === 'unpaid') {
    $filterQuery = "AND s.SummonStatus != 'Paid'";
}

/* --------------------------------
   FETCH SUMMON LIST
----------------------------------*/
// Ambil senarai saman pelajar berdasarkan filter yang dipilih
$sqlSummons = "
    SELECT s.*, v.PlateNumber, vt.ViolationName, vt.ViolationPoints
    FROM Summon s
    JOIN Vehicle v ON s.VehicleID = v.VehicleID
    JOIN ViolationType vt ON s.ViolationTypeID = vt.ViolationTypeID
    WHERE v.UserID = '$userID' $filterQuery
    ORDER BY s.SummonDate DESC, s.SummonTime DESC
";
$summons = mysqli_query($conn, $sqlSummons);

/* --------------------------------
   FETCH LATEST PUNISHMENT DETAILS
----------------------------------*/
/*
Ambil rekod punishment terbaru untuk pelajar ini.
Jika mata < 20, tiada punishment akan dikenakan.
*/
if ($totalPoints < 20) {
    $punishStart  = '-';
    $punishEnd    = '-';
    $punishStatus = 'None';
} else {
    $sqlPunish = "
        SELECT *
        FROM PunishmentDuration
        WHERE UserID = '$userID'
        ORDER BY PunishmentDurationID DESC
        LIMIT 1
    ";
    $resPunish = mysqli_query($conn, $sqlPunish);
    $punish = mysqli_fetch_assoc($resPunish);

    if ($punish) {
        $punishStart  = $punish['StartDate'] ?? '-';
        $punishEnd    = $punish['EndDate'] ?? '-';
        $punishStatus = $punish['Status'] ?? 'None';
    } else {
        // no record found even though points >= 20
        $punishStart  = '-';
        $punishEnd    = '-';
        $punishStatus = 'None';
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Demerit Points - FKPark</title>
    <link rel="stylesheet" href="../templates/student_style.css">

    <style>
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #0A3D62;
            background: #DCEAFF;
            padding: 10px 15px;
            border-radius: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        .detail-row {
            display: flex;
            align-items: center;
            margin: 8px 0;
        }

        .detail-label {
            width: 200px;
            font-weight: 700;
            color: #0A3D62;
        }

        .detail-value {
            font-weight: 500;
            color: #0A3D62;
        }

        .divider {
            height: 1px;
            background: #BBD4FF;
            margin: 15px 0;
            width: 100%;
        }

        .section-sub-box {
            background: #DCEAFF;
            border-left: 6px solid #6BA8FF;
            padding: 15px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .section-sub-title {
            font-size: 18px;
            font-weight: 700;
            color: #0A3D62;
        }

        .section-sub-text {
            font-size: 15px;
            color: #3A607E;
        }
    </style>
</head>

<body>
    <?php include '../templates/student_sidebar.php'; ?>

    <div class="main-content">

        <div class="header">⚠️ My Demerit Points</div>

        <div class="container">

            <!-- Header ringkas untuk paparan mata demerit -->
            <!-- HEADER BOX -->
            <div class="section-sub-box">
                <div class="section-sub-title">Demerit Points & Punishment Status</div>
                <div class="section-sub-text">Track your accumulated demerit points and current punishment status.</div>
            </div>

            <div class="box">

                <!-- SECTION TITLE -->
                <div class="section-title">Demerit Points & Punishment Status</div>

                <!-- Papar kenderaan pelajar -->
                <!-- VEHICLE -->
                <div class="detail-row">
                    <span class="detail-label">Vehicle:</span>
                    <span class="detail-value">
                        <?php
                        $vehQuery = mysqli_query($conn, "SELECT PlateNumber FROM Vehicle WHERE UserID = '$userID' LIMIT 1");
                        $vehRow = mysqli_fetch_assoc($vehQuery);
                        echo $vehRow ? $vehRow['PlateNumber'] : "No vehicle registered";
                        ?>
                    </span>
                </div>

                <!-- Papar jumlah mata demerit -->
                <!-- TOTAL POINTS -->
                <div class="detail-row">
                    <span class="detail-label">Total Demerit Points:</span>
                    <span class="detail-value" style="font-size:18px;">
                        <?= $totalPoints ?>
                    </span>
                </div>

                <!-- Papar status punishment berdasarkan jumlah mata -->
                <!-- PUNISHMENT STATUS -->
                <div class="detail-row">
                    <span class="detail-label">Punishment Status:</span>
                    <span class="detail-value" style="font-size:18px;">
                        <?php
                        if ($totalPoints < 20) {
                            echo "Warning";
                        } elseif ($totalPoints < 50) {
                            echo "Vehicle Revoked (1 Semester)";
                        } elseif ($totalPoints < 80) {
                            echo "Vehicle Revoked (2 Semesters)";
                        } else {
                            echo "Vehicle Ban (Entire Study Duration)";
                        }
                        ?>
                    </span>
                </div>

                <!-- Tarikh mula dan tamat punishment -->
                <!-- Start Date -->
                <div class="detail-row">
                    <span class="detail-label">Start Date:</span>
                    <span class="detail-value">
                        <?= ($punishStart !== '-' ? date('d/m/Y', strtotime($punishStart)) : '-') ?>
                    </span>
                </div>

                <!-- End Date -->
                <div class="detail-row">
                    <span class="detail-label">End Date:</span>
                    <span class="detail-value">
                        <?= ($punishEnd !== '-' ? date('d/m/Y', strtotime($punishEnd)) : '-') ?>
                    </span>
                </div>

                <!-- Record Status -->
                <div class="detail-row">
                    <span class="detail-label">Record Status:</span>
                    <span class="detail-value"
                        style="font-weight:700;
                               color:<?= $punishStatus == 'Active' ? 'green' : ($punishStatus == 'Completed' ? 'gray' : '#0A3D62') ?>;">
                        <?= $punishStatus ?>
                    </span>
                </div>

                <div class="divider"></div>

                <!-- Senarai peraturan punishment berdasarkan mata demerit -->
                <!-- PUNISHMENT RULES -->
                <div class="section-title">Punishment Rules</div>

                <div class="detail-row">
                    <span class="detail-label">Less than 20 points:</span>
                    <span class="detail-value">Warning</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">20 - 49 points:</span>
                    <span class="detail-value">Vehicle Revoked (1 Semester)</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">50 - 79 points:</span>
                    <span class="detail-value">Vehicle Revoked (2 Semesters)</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">80+ points:</span>
                    <span class="detail-value">Vehicle Ban (Entire Study Duration)</span>
                </div>

                <div class="divider"></div>

                <!-- Senarai saman pelajar -->
                <!-- SUMMONS -->
                <div class="section-title">My Summons</div>

                <div style="margin-bottom:15px;">
                    <a href="student_demerit_points.php?filter=all" style="font-weight:700; margin-right:10px;">[All]</a>
                    <a href="student_demerit_points.php?filter=unpaid" style="font-weight:700; margin-right:10px;">[Unpaid]</a>
                    <a href="student_demerit_points.php?filter=paid" style="font-weight:700;">[Paid]</a>
                </div>

                <?php if (mysqli_num_rows($summons) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($summons)): ?>

                        <div class="detail-row">
                            <span class="detail-label">Summon ID:</span>
                            <span class="detail-value"><strong><?= $row['SummonID']; ?></strong></span>
                        </div>

                        <div class="detail-row">
                            <span class="detail-label">Violation:</span>
                            <span class="detail-value"><?= $row['ViolationName']; ?></span>
                        </div>

                        <div class="detail-row">
                            <span class="detail-label">Points:</span>
                            <span class="detail-value"><strong>+<?= $row['ViolationPoints']; ?></strong></span>
                        </div>

                        <div class="detail-row">
                            <span class="detail-label">Date:</span>
                            <span class="detail-value"><?= date('d/m/Y', strtotime($row['SummonDate'])); ?></span>
                        </div>

                        <div class="detail-row">
                            <span class="detail-label">Location:</span>
                            <span class="detail-value"><?= $row['Location']; ?></span>
                        </div>

                        <!-- Action: bayar saman jika masih unpaid -->
                        <div class="detail-row">
                            <span class="detail-label">Action:</span>
                            <span class="detail-value">
                                <?php if (strtolower($row['SummonStatus']) !== 'paid'): ?>
                                    <a href="student_pay_summon.php?id=<?= $row['SummonID']; ?>" style="font-weight:700; color:#1B73E8;">[Pay]</a>
                                <?php else: ?>
                                    <span style="color:gray; font-weight:600;">[No Action Needed]</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value">
                                <?php if (strtolower($row['SummonStatus']) === 'paid'): ?>
                                    <span style="color:green; font-weight:700;">Paid</span>
                                <?php else: ?>
                                    <span style="color:red; font-weight:700;">Unpaid</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="divider"></div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No summons found.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script>
        // Fix issue back button (reload page bila cached)
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