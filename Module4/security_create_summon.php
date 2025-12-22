<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';
require_once 'phpqrcode/phpqrcode.php';

if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../login.php");
    exit();
}

// Handle submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // MUST exist now because hidden input is inside form
    $vehicleID = $_POST['vehicleID'];

    if (empty($vehicleID)) {
        die("ERROR: No vehicle selected. Please search by plate number first.");
    }

    $violationTypeID = $_POST['violationTypeID'];
    $summonDate = $_POST['summonDate'];
    $summonTime = $_POST['summonTime'];
    $location = $_POST['location'];

    // evidence upload
    $uploadDir = "../uploads/";
    $fileName = basename($_FILES["evidence"]["name"]);
    $serverPath = $uploadDir . $fileName;
    $publicURL = "http://localhost/WebEng/FKPark/uploads/" . $fileName;

    if (!empty($_FILES["evidence"]["name"])) {
        if (!move_uploaded_file($_FILES["evidence"]["tmp_name"], $serverPath)) {
            die("ERROR: Unable to upload evidence file. Check folder permissions.");
        }
    }

    $sqlInsert = "
        INSERT INTO Summon (VehicleID, ViolationTypeID, SummonDate, SummonTime, Location, Evidence, SummonStatus)
        VALUES ('$vehicleID', '$violationTypeID', '$summonDate', '$summonTime', '$location', '$publicURL', 'Unpaid')
    ";

    mysqli_query($conn, $sqlInsert);

    $summonID = mysqli_insert_id($conn);

    // === GENERATE QR CODE ===
    $qrFolder = "../uploads/qr/";

    // Make folder if not exists
    if (!is_dir($qrFolder)) {
        mkdir($qrFolder, 0777, true);
    }

    $qrFilename = "summon_" . $summonID . ".png";
    $qrFilepath = $qrFolder . $qrFilename;

    // The data encoded inside the QR code
    $qrData = "http://localhost/WebEng/FKPark/Module4/security_summon_view.php?id=" . $summonID;

    // Generate PNG QR Code
    QRcode::png($qrData, $qrFilepath, QR_ECLEVEL_L, 6);

    // Save QR path into SummonQRCode table
    mysqli_query($conn, "
    INSERT INTO SummonQRCode (SummonID, QRCodeData)
    VALUES ('$summonID', '$qrFilepath')
");

    // === ENFORCEMENT LOGIC START ===

    // 1. Get StudentID from Vehicle
    $qStudent = mysqli_query(
        $conn,
        "SELECT UserID FROM Vehicle WHERE VehicleID='$vehicleID' LIMIT 1"
    );
    $studentRow = mysqli_fetch_assoc($qStudent);
    $userID = $studentRow['UserID'];

    // 2. Recalculate total demerit points
    $qPts = mysqli_query($conn, "
        SELECT SUM(VT.ViolationPoints) AS totalPts
        FROM Summon S
        LEFT JOIN ViolationType VT ON S.ViolationTypeID = VT.ViolationTypeID
        LEFT JOIN Vehicle V ON S.VehicleID = V.VehicleID
        WHERE V.UserID='$userID'
    ");
    $ptsRow = mysqli_fetch_assoc($qPts);
    $totalPts = intval($ptsRow['totalPts']);

    // Update Student table with new total demerit points
    mysqli_query($conn, "
        UPDATE User 
        SET TotalDemeritPoints = '$totalPts'
        WHERE UserID = '$userID'
    ");

    // 3. Determine enforcement type + duration
    $enforcementType = "";
    $startDate = $summonDate;
    $endDate = "NULL"; // default for permanent

    if ($totalPts < 20) {
        $enforcementType = "Warning";
        $endDateSQL = "NULL";
    } elseif ($totalPts < 50) {
        $enforcementType = "Revoke 1 Semester";
        $endDateSQL = "'" . date('Y-m-d', strtotime($startDate . ' +6 months')) . "'";
    } elseif ($totalPts < 80) {
        $enforcementType = "Revoke 2 Semesters";
        $endDateSQL = "'" . date('Y-m-d', strtotime($startDate . ' +12 months')) . "'";
    } else {
        $enforcementType = "Revoke Permanent";
        $endDateSQL = "NULL";
    }

    // 4. Insert enforcement record (keep history)
    mysqli_query($conn, "
        INSERT INTO Enforcement (UserID, EnforcementType, StartDate, EndDate, Status)
        VALUES ('$userID', '$enforcementType', '$startDate', $endDateSQL, 'Active')
    ");
    // === ENFORCEMENT LOGIC END ===

    header("Location: security_summon_success.php?id=" . $summonID);
    exit();
}

$violations = mysqli_query($conn, "SELECT * FROM ViolationType");
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

        input[type="date"],
        input[type="time"] {
            display: block;
            width: 100%;
        }

        input[type="file"] {
            background: #fff;
            border: 1px solid #ECCB7E;
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

        #plateNumber {
            border: 1px solid #FFD972;
            background: #FFF7CD;
        }

        #plateNumber:focus {
            outline: none;
            border-color: #FFC93C;
            background: #FFF3B8;
        }

        .center-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            padding-right: 40px;
            /* balances layout */
        }
    </style>
    <script>
        let searchTimeout = null;

        document.addEventListener("DOMContentLoaded", function() {
            const plateInput = document.getElementById("plateNumber");

            plateInput.addEventListener("input", function() {
                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(() => {
                    const plate = plateInput.value.trim();
                    if (plate.length < 3) return;

                    fetch("search_vehicle.php?plate=" + encodeURIComponent(plate))
                        .then(res => res.json())
                        .then(data => {
                            const box = document.getElementById("student-info-box");
                            box.style.display = "block";

                            if (data.status === "success" && data.data) {
                                box.innerHTML = `
                                    <p><b>Name:</b> ${data.data.UserName ?? "-"}</p>
                                    <p><b>User ID:</b> ${data.data.UserID ?? "-"}</p>
                                    <p><b>Program:</b> ${data.data.StudentProgram ?? "-"}</p>
                                    <p><b>Year:</b> ${data.data.StudentYear ?? "-"}</p>
                                    <p><b>Total Demerit:</b> ${data.data.TotalDemeritPoints ?? 0}</p>
                                    <p><b>Enforcement:</b> ${data.data.EnforcementStatus ?? "None"}</p>
                                `;
                                document.getElementById("vehicleID").value = data.data.VehicleID ?? "";
                            } else {
                                box.innerHTML = "<b>No vehicle found for this plate.</b>";
                                document.getElementById("vehicleID").value = "";
                            }
                        })
                        .catch(err => console.error(err));
                }, 400);
            });
        });
    </script>
</head>

<body>

    <?php include '../templates/security_sidebar.php'; ?>

    <div class="main-content">
        <div class="header">➕ Issue Summon</div>

        <div class="form-box">

            <h2>Search Vehicle</h2>

            <!-- FORM START -->
            <form method="POST" enctype="multipart/form-data">

                <label>Plate Number</label>
                <input type="text" id="plateNumber" name="plateNumber" required>
                <div id="student-info-box"></div>

                <!-- MUST BE INSIDE FORM -->
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