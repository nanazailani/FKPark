<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Restrict to admin only
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Administrator') {
    header("Location: ../index.php");
    exit();
}

// Make sure an ID is provided
if (!isset($_GET['id'])) {
    header("Location: user_list.php");
    exit();
}

$userID = $_GET['id'];

// Fetch user info
$sql = mysqli_query($conn, "SELECT * FROM User WHERE UserID = '$userID'");
$user = mysqli_fetch_assoc($sql);

if (!$user) {
    echo "<script>alert('User not found!'); window.location='user_list.php';</script>";
    exit();
}

//
// ===== UPDATE USER =====
//
if (isset($_POST['update'])) {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $passwordInput = $_POST['password'];

    // If admin typed a new password → hash it
    if (!empty($passwordInput)) {
        $hashedPassword = password_hash($passwordInput, PASSWORD_DEFAULT);
    } else {
        // Keep old password if field is empty
        $hashedPassword = $user['UserPassword'];
    }

    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update = "
        UPDATE User
        SET UserName='$name',
            UserEmail='$email',
            UserPassword='$hashedPassword',
            UserRole='$role'
        WHERE UserID='$userID'
    ";


    mysqli_query($conn, $update);

    echo "<script>
        alert('Profile Updated Successfully!');
        window.location='admin_user_view.php?id=$userID';
    </script>";
    exit();
}

//
// ===== DELETE USER =====
//
if (isset($_POST['delete'])) {

    $id = mysqli_real_escape_string($conn, $_POST['userid']);

    // Get role
    $roleQuery = mysqli_query($conn, "SELECT UserRole FROM user WHERE UserID='$id'");
    $roleData  = mysqli_fetch_assoc($roleQuery);
    $role      = $roleData['UserRole'];

    // ============================
    // IF STUDENT
    // ============================
    if ($role === "Student") {

        // 1️⃣ Get all vehicle IDs owned by student
        $vehicleIDs = [];
        $getVehicles = mysqli_query($conn, "
            SELECT VehicleID FROM vehicle WHERE UserID='$id'
        ");

        while ($v = mysqli_fetch_assoc($getVehicles)) {
            $vehicleIDs[] = $v['VehicleID'];
        }

        if (!empty($vehicleIDs)) {
            $vIDs = "'" . implode("','", $vehicleIDs) . "'";

            // 2️⃣ Get summons linked to vehicles
            $summonIDs = [];
            $getSummons = mysqli_query($conn, "
                SELECT SummonID FROM summon WHERE VehicleID IN ($vIDs)
            ");

            while ($s = mysqli_fetch_assoc($getSummons)) {
                $summonIDs[] = $s['SummonID'];
            }

            if (!empty($summonIDs)) {
                $sIDs = implode(",", $summonIDs);

                mysqli_query($conn, "DELETE FROM summonqrcode WHERE SummonID IN ($sIDs)");
                mysqli_query($conn, "DELETE FROM demerit WHERE SummonID IN ($sIDs)");
                mysqli_query($conn, "DELETE FROM summon WHERE SummonID IN ($sIDs)");
            }

            // 3️⃣ Delete vehicles
            mysqli_query($conn, "DELETE FROM vehicle WHERE VehicleID IN ($vIDs)");
        }

        // 4️⃣ Booking related
        mysqli_query($conn, "DELETE FROM bookingqrcode 
            WHERE BookingID IN (SELECT BookingID FROM booking WHERE UserID='$id')");
        mysqli_query($conn, "DELETE FROM parkinglog 
            WHERE BookingID IN (SELECT BookingID FROM booking WHERE UserID='$id')");
        mysqli_query($conn, "DELETE FROM booking WHERE UserID='$id'");

        // 5️⃣ Punishment & student profile
        mysqli_query($conn, "DELETE FROM punishmentduration WHERE UserID='$id'");
        mysqli_query($conn, "DELETE FROM student WHERE UserID='$id'");
    }

    // ============================
    // IF SECURITY STAFF
    // ============================
    if ($role === "Security Staff") {

        // Remove approval reference
        mysqli_query($conn, "
            UPDATE vehicle SET ApprovedBy = NULL WHERE ApprovedBy='$id'
        ");

        // Remove staff profile
        mysqli_query($conn, "DELETE FROM securitystaff WHERE UserID='$id'");
    }

    // ============================
    // DELETE USER ACCOUNT (LAST)
    // ============================
    mysqli_query($conn, "DELETE FROM user WHERE UserID='$id'");

    echo "<script>
        alert('User account deleted successfully!');
        window.location='admin_user_list.php';
    </script>";
    exit();
}





?>
<!DOCTYPE html>
<html>

<head>
    <title>View User</title>
    <link rel="stylesheet" href="../templates/admin_style.css">

    <style>
        .view-box {
            background: #fff;
            padding: 25px;
            border-radius: 20px;
            width: 70%;
            border-left: 8px solid #FFB873;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        label {
            font-weight: 600;
            color: #773f00;
        }

        input,
        select {
            width: 95%;
            padding: 12px;
            border: 1px solid #FFB873;
            border-radius: 12px;
            background: #FFEEDB;
            margin-bottom: 15px;
        }

        .btn-save {
            background: #FF9A3C;
            padding: 12px 20px;
            color: white;
            border-radius: 12px;
            font-weight: 700;
            border: none;
        }

        .btn-delete {
            background: #C40000;
            padding: 12px 20px;
            color: white;
            border-radius: 12px;
            margin-left: 10px;
            font-weight: 700;
            border: none;
        }
    </style>
</head>

<body>

    <?php include '../templates/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="header">👤 View/Update User Profile</div>

        <div class="view-box">
            <form method="POST">

                <!-- Hidden ID for deletion -->
                <input type="hidden" name="userid" value="<?= $user['UserID'] ?>">

                <label>User ID</label>
                <input type="text" value="<?= $user['UserID'] ?>" disabled>

                <label>Name</label>
                <input type="text" name="name" value="<?= $user['UserName'] ?>" required>

                <label>Email</label>
                <input type="email" name="email" value="<?= $user['UserEmail'] ?>" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="Enter new password (leave blank to keep current)">

                <label>User Role</label>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="Administrator" <?= $user['UserRole'] === 'Administrator' ? 'selected' : '' ?>>
                        Administrator
                    </option>
                    <option value="Student" <?= $user['UserRole'] === 'Student' ? 'selected' : '' ?>>
                        Student
                    </option>
                    <option value="Security Staff" <?= $user['UserRole'] === 'Security Staff' ? 'selected' : '' ?>>
                        Security Staff
                    </option>
                </select>

                <button type="submit" name="update" class="btn-save">💾 Save Changes</button>

                <button type="submit" name="delete" class="btn-delete"
                    onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone!');">
                    🗑 Delete User
                </button>

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