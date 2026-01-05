<?php
// Start session untuk simpan maklumat login (UserRole, UserID, etc)
// Enable semua error supaya senang debug masa development
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Include config.php untuk sambung ke database
require_once '../config.php';
// Load library phpqrcode untuk generate QR code
require_once __DIR__ . '/phpqrcode/phpqrcode.php';

// Security check: hanya Security Staff dibenarkan akses page ini
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] !== 'Security Staff') {
    header("Location: ../index.php");
    exit();
}

// Ambil senarai jenis kesalahan untuk dropdown
$violations = mysqli_query($conn, "SELECT * FROM ViolationType");

// Handle form submission bila tekan butang "Create Summon"
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Pastikan vehicle telah dipilih melalui carian plate number
    $vehicleID = $_POST['vehicleID'] ?? '';

    if (empty($vehicleID)) {
        die("ERROR: No vehicle selected. Please search by plate number first.");
    }

    $violationTypeID = $_POST['violationTypeID'] ?? '';
    $summonDate = $_POST['summonDate'] ?? '';
    $summonTime = $_POST['summonTime'] ?? '';
    $location = $_POST['location'] ?? '';

    // Proses upload bukti (gambar / file)
    $uploadDir = "../uploads/";
    $fileName = basename($_FILES["evidence"]["name"] ?? '');
    $serverPath = $uploadDir . $fileName;

    // Build a public URL (adjust if your project base URL differs)
    $publicURL = $fileName ? ("http://localhost/FKPark/uploads/" . $fileName) : "";

    if ($fileName) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if (!move_uploaded_file($_FILES["evidence"]["tmp_name"], $serverPath)) {
            die("ERROR: Unable to upload evidence file. Check folder permissions.");
        }
    }

    // Simpan maklumat saman ke dalam table Summon
    $sqlInsert = "
        INSERT INTO Summon (VehicleID, ViolationTypeID, SummonDate, SummonTime, Location, Evidence, SummonStatus)
        VALUES ('$vehicleID', '$violationTypeID', '$summonDate', '$summonTime', '$location', '$publicURL', 'Unpaid')
    ";
    mysqli_query($conn, $sqlInsert);
    $summonID = mysqli_insert_id($conn);

    // Setiap saman akan create satu rekod demerit
    // Mata demerit diambil dari ViolationType
    $getPoints = mysqli_query($conn, "
        SELECT ViolationPoints 
        FROM ViolationType 
        WHERE ViolationTypeID = '$violationTypeID'
    ");
    $pointsRow = mysqli_fetch_assoc($getPoints);
    $points = (int)($pointsRow['ViolationPoints'] ?? 0);

    mysqli_query($conn, "
        INSERT INTO Demerit (SummonID, DemeritPoints, IssuedDate, Status)
        VALUES ('$summonID', '$points', CURDATE(), 'Active')
    ");

    // ================= ENFORCEMENT LOGIC (AUTO) =================
    // Kira jumlah mata demerit dan tentukan tindakan automatik

    // Dapatkan UserID pemilik kenderaan berdasarkan VehicleID
    $getUser = mysqli_query($conn, "
        SELECT UserID FROM Vehicle WHERE VehicleID = '$vehicleID'
    ");
    $userRow = mysqli_fetch_assoc($getUser);
    $userID = $userRow['UserID'] ?? null;

    if ($userID) {

        // Kira jumlah keseluruhan mata demerit untuk user ini
        $getTotal = mysqli_query($conn, "
            SELECT SUM(d.DemeritPoints) AS TotalPoints
            FROM Demerit d
            JOIN Summon s ON d.SummonID = s.SummonID
            JOIN Vehicle v ON s.VehicleID = v.VehicleID
            WHERE v.UserID = '$userID'
        ");
        $totalRow = mysqli_fetch_assoc($getTotal);
        $totalPoints = (int)($totalRow['TotalPoints'] ?? 0);

        // Nyahaktifkan enforcement lama supaya hanya satu yang aktif
        mysqli_query($conn, "
            UPDATE PunishmentDuration
            SET Status = 'Inactive'
            WHERE UserID = '$userID' AND Status = 'Active'
        ");

        // Tentukan jenis tindakan berdasarkan jumlah mata demerit
        if ($totalPoints < 20) {
            // Warning only (no DB insert)
        } elseif ($totalPoints < 50) {
            mysqli_query($conn, "
                INSERT INTO PunishmentDuration
                (PunishmentType, StartDate, EndDate, Status, UserID)
                VALUES
                ('Vehicle Revoked (1 Semester)', CURDATE(),
                 DATE_ADD(CURDATE(), INTERVAL 6 MONTH),
                 'Active', '$userID')
            ");
        } elseif ($totalPoints < 80) {
            mysqli_query($conn, "
                INSERT INTO PunishmentDuration
                (PunishmentType, StartDate, EndDate, Status, UserID)
                VALUES
                ('Vehicle Revoked (2 Semesters)', CURDATE(),
                 DATE_ADD(CURDATE(), INTERVAL 12 MONTH),
                 'Active', '$userID')
            ");
        } else {
            mysqli_query($conn, "
                INSERT INTO PunishmentDuration
                (PunishmentType, StartDate, EndDate, Status, UserID)
                VALUES
                ('Vehicle Revoked (Entire Study)', CURDATE(),
                 '2099-12-31',
                 'Active', '$userID')
            ");
        }
    }

    // ================= QR CODE GENERATION =================
    // Generate QR code untuk view saman

    // Pastikan folder qrcodes wujud
    $qrDir = __DIR__ . '/qrcodes/';
    if (!is_dir($qrDir)) {
        mkdir($qrDir, 0777, true);
    }

    $qrText = "http://localhost/FKPark/Module4/student_view_summon.php?summon_id=" . $summonID;
    $qrFileName = "summon_" . $summonID . ".png";
    $qrFilePath = $qrDir . $qrFileName;

    // Generate fail QR code dalam bentuk image PNG
    QRcode::png($qrText, $qrFilePath, QR_ECLEVEL_L, 6);

    // Add QR code generation safety check
    if (!file_exists($qrFilePath)) {
        die("ERROR: QR code generation failed.");
    }

    // Simpan path QR code ke dalam database
    $qrPublicPath = "../Module4/qrcodes/" . $qrFileName;

    mysqli_query($conn, "
        INSERT INTO SummonQRCode (SummonID, QRCodeData, GenerateDate)
        VALUES ('$summonID', '$qrPublicPath', NOW())
    ");

    // Redirect ke page success selepas saman berjaya dicipta
    header("Location: security_summon_success.php?id=" . $summonID);
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Issue Summon</title>
    <link rel="stylesheet" href="../templates/security_style.css">
    <style>
        .form-box {
            background: #ffffff;
            padding: 25px;
            border-radius: 20px;
            width: 95%;
            margin-top: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border-left: 8px solid #FFE28A;
        }

        .form-box h2 {
            color: #5A4B00;
            margin-bottom: 20px;
            font-weight: 700;
            margin-top: -10px;
        }

        label {
            font-weight: 600;
            color: #5A4B00;
        }

        input,
        select {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #ECCB7E;
            background: #FFF9D7;
            margin-bottom: 15px;
            padding-left: 14px;
            padding-right: 14px;
            margin-top: 10px;
        }

        input[type="file"] {
            background: #fff;
        }

        #student-info-box {
            background: #FFF7C8;
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 15px;
            border: 1px solid #FFE08C;
            display: none;
            color: #5A4B00;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        button {
            background: #FFC93C;
            color: #5A4B00;
            padding: 12px 22px;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.2s ease;
            margin-top: 10px;
        }

        button:hover {
            background: #FFBB22;
            transform: scale(1.03);
        }
    </style>

    <!-- Script untuk carian plate number secara live (AJAX) -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const plateInput = document.getElementById("plateNumber");
            const infoBox = document.getElementById("student-info-box");
            const vehicleInput = document.getElementById("vehicleID");

            let searchTimeout = null;

            plateInput.addEventListener("input", function() {
                clearTimeout(searchTimeout);

                infoBox.style.display = "block";
                infoBox.innerHTML = "Searching vehicle...";

                searchTimeout = setTimeout(() => {
                    const plate = plateInput.value.trim();

                    if (plate.length < 3) {
                        infoBox.innerHTML = "Enter at least 3 characters.";
                        vehicleInput.value = "";
                        return;
                    }

                    // Hantar plate number ke backend dan dapatkan maklumat kenderaan
                    fetch("search_vehicle.php?plate=" + encodeURIComponent(plate))
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === "success" && data.data) {
                                infoBox.innerHTML = `
                                <p><b>Name:</b> ${data.data.UserName ?? "-"}</p>
                                <p><b>User ID:</b> ${data.data.UserID ?? "-"}</p>
                                <p><b>Program:</b> ${data.data.StudentProgram ?? "-"}</p>
                                <p><b>Year:</b> ${data.data.StudentYear ?? "-"}</p>
                                <p><b>Total Demerit:</b> ${data.data.TotalDemeritPoints ?? 0}</p>
                                <p><b>Enforcement:</b> ${data.data.EnforcementStatus ?? "None"}</p>
                            `;
                                vehicleInput.value = data.data.VehicleID ?? "";
                            } else {
                                infoBox.innerHTML = "No vehicle found for this plate.";
                                vehicleInput.value = "";
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            infoBox.innerHTML = "Error searching vehicle.";
                            vehicleInput.value = "";
                        });
                }, 400);
            });
        });
    </script>
</head>

<body>
    <?php include '../templates/security_sidebar.php'; ?>

    <div class="main-content">
        <div class="header">+ Issue Summon</div>

        <div class="form-box">
            <h2>Search Vehicle</h2>

            <form method="POST" enctype="multipart/form-data">
                <label>Plate Number</label>
                <input type="text" id="plateNumber" name="plateNumber" required>
                <div id="student-info-box"></div>

                <input type="hidden" name="vehicleID" id="vehicleID">

                <label>Violation Type</label>
                <select name="violationTypeID" required>
                    <option value="">Select violation</option>
                    <?php while ($vt = mysqli_fetch_assoc($violations)): ?>
                        <option value="<?= $vt['ViolationTypeID'] ?>">
                            <?= $vt['ViolationName'] ?> (<?= $vt['ViolationPoints'] ?> pts)
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Date</label>
                <input type="date" name="summonDate" required>

                <label>Time</label>
                <input type="time" name="summonTime" required>

                <label>Location</label>
                <input type="text" name="location" required>

                <label>Evidence</label>
                <input type="file" name="evidence" required>

                <button type="submit">Create Summon</button>
            </form>
        </div>
    </div>
</body>

</html>