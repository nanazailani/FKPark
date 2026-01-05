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

// Generate VehicleID (V001, V002...)
$getLast = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT VehicleID FROM Vehicle ORDER BY VehicleID DESC LIMIT 1
"));

if ($getLast) {
    $num = intval(substr($getLast['VehicleID'], 1)) + 1;
    $newVehicleID = "V" . str_pad($num, 3, "0", STR_PAD_LEFT);
} else {
    $newVehicleID = "V001";
}

// Handle submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $plate = mysqli_real_escape_string($conn, $_POST['plate']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);

    // Upload folder
    $uploadDir = "../uploads/vehicle_grants/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES["grant"]["name"]);
    $savedPath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES["grant"]["tmp_name"], $savedPath)) {
        die("ERROR: Failed to upload vehicle grant image.");
    }

    // Save public URL
    $publicURL = "../uploads/vehicle_grants/" . $fileName;

    // Insert into database
    $sql = "
    INSERT INTO Vehicle (VehicleID, UserID, PlateNumber, VehicleType, VehicleGrant, ApprovalStatus)
    VALUES ('$newVehicleID', '$studentID', '$plate', '$type', '$publicURL', 'Pending')
";


    mysqli_query($conn, $sql);

    echo "<script>
        alert('Vehicle registered successfully! Waiting for approval.');
        window.location='student_dashboard.php';
    </script>";
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Register Vehicle</title>
    <link rel="stylesheet" href="../templates/student_style.css">

    <style>
        .form-box {
            background: #fff;
            padding: 25px;
            border-radius: 20px;
            width: 70%;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border-left: 8px solid #5B9BFF;
        }

        label {
            font-weight: 600;
            color: #003A75;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #5B9BFF;
            background: #EAF3FF;
            margin-bottom: 15px;
        }

        input[type="file"] {
            background: #fff;
        }

        button {
            background: #5B9BFF;
            color: white;
            padding: 12px 22px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
        }

        button:hover {
            background: #3279FF;
        }
    </style>
</head>

<body>

    <?php include '../templates/student_sidebar.php'; ?>

    <div class="main-content">
        <div class="header">🚗 Register Vehicle</div>

        <div class="form-box">

            <form method="POST" enctype="multipart/form-data">

                <label>Vehicle ID</label>
                <input type="text" value="<?= $newVehicleID ?>" disabled>

                <label>Plate Number</label>
                <input type="text" name="plate" placeholder="e.g. ABC1234" required>

                <label>Vehicle Type</label>
                <select name="type" required>
                    <option value="">Select Type</option>
                    <option value="Car">Car</option>
                    <option value="Motorcycle">Motorcycle</option>
                </select>

                <label>Upload Vehicle Grant (Image)</label>
                <input type="file" name="grant" accept="image/*" required>

                <button type="submit">Register Vehicle</button>

            </form>

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