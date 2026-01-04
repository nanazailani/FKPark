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
    $type  = mysqli_real_escape_string($conn, $_POST['type']);

    $uploadDir = "../uploads/vehicle_grants/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES["grant"]["name"]);
    $savedPath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES["grant"]["tmp_name"], $savedPath)) {
        die("ERROR: Failed to upload vehicle grant image.");
    }

    $publicURL = "../uploads/vehicle_grants/" . $fileName;

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Vehicle</title>

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
            padding: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .form-control,
        .form-select {
            background: #EAF3FF;
            border: 1px solid #5B9BFF;
            border-radius: 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3279FF;
            box-shadow: none;
        }

        .btn-custom {
            background: #5B9BFF;
            color: white;
            font-weight: 700;
            padding: 12px 22px;
            border-radius: 12px;
            border: none;
        }

        .btn-custom:hover {
            background: #3279FF;
        }
    </style>
</head>

<body class="bg-light">

<?php include '../templates/student_sidebar.php'; ?>

<div class="main-content">

    <div class="container mt-4">

        <div class="header mb-4">🚗 Register Vehicle</div>

        <div class="card vehicle-card">
            <div class="card-body p-0">

                <form method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label>Vehicle ID</label>
                        <input type="text" class="form-control" value="<?= $newVehicleID ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label>Plate Number</label>
                        <input type="text" name="plate" class="form-control" placeholder="e.g. ABC1234" required>
                    </div>

                    <div class="mb-3">
                        <label>Vehicle Type</label>
                        <select name="type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="Car">Car</option>
                            <option value="Motorcycle">Motorcycle</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Upload Vehicle Grant (Image)</label>
                        <input type="file" name="grant" class="form-control" accept="image/*" required>
                    </div>

                    <button type="submit" class="btn btn-custom">
                        Register Vehicle
                    </button>

                </form>

            </div>
        </div>

    </div>
</div>

<script>
    // Prevent cached access after logout
    window.addEventListener("pageshow", function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>

</body>
</html>
