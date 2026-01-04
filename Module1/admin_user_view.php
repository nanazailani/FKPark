<?php
// start session
session_start();

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// database connection
require_once '../config.php';

// check if administrator
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Administrator') {
    header("Location: ../Module1/login.php");
    exit();
}

// make sure id exists
if (!isset($_GET['id'])) {
    header("Location: admin_user_list.php");
    exit();
}

$userID = $_GET['id'];

// get user data
$sql  = mysqli_query($conn, "SELECT * FROM User WHERE UserID = '$userID'");
$user = mysqli_fetch_assoc($sql);

// if user not found
if (!$user) {
    echo "<script>alert('User not found!'); window.location='admin_user_list.php';</script>";
    exit();
}

// ================= UPDATE USER =================
if (isset($_POST['update'])) {

    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $hashedPassword = $user['UserPassword'];
    }

    $role = mysqli_real_escape_string($conn, $_POST['role']);

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

// ================= DELETE USER =================
if (isset($_POST['delete'])) {

    $id = mysqli_real_escape_string($conn, $_POST['userid']);

    $roleQuery = mysqli_query($conn, "SELECT UserRole FROM user WHERE UserID='$id'");
    $roleData  = mysqli_fetch_assoc($roleQuery);
    $role      = $roleData['UserRole'];

    // ---------- STUDENT ----------
    if ($role === "Student") {

        $vehicleIDs = [];
        $getVehicles = mysqli_query($conn, "
            SELECT VehicleID FROM vehicle WHERE UserID='$id'
        ");

        while ($v = mysqli_fetch_assoc($getVehicles)) {
            $vehicleIDs[] = $v['VehicleID'];
        }

        if (!empty($vehicleIDs)) {
            $vIDs = "'" . implode("','", $vehicleIDs) . "'";

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

            mysqli_query($conn, "DELETE FROM vehicle WHERE VehicleID IN ($vIDs)");
        }

        mysqli_query($conn, "DELETE FROM bookingqrcode WHERE BookingID IN (SELECT BookingID FROM booking WHERE UserID='$id')");
        mysqli_query($conn, "DELETE FROM parkinglog WHERE BookingID IN (SELECT BookingID FROM booking WHERE UserID='$id')");
        mysqli_query($conn, "DELETE FROM booking WHERE UserID='$id'");
        mysqli_query($conn, "DELETE FROM punishmentduration WHERE UserID='$id'");
        mysqli_query($conn, "DELETE FROM student WHERE UserID='$id'");
    }

    // ---------- SECURITY STAFF ----------
    if ($role === "Security Staff") {
        mysqli_query($conn, "UPDATE vehicle SET ApprovedBy = NULL WHERE ApprovedBy='$id'");
        mysqli_query($conn, "DELETE FROM securitystaff WHERE UserID='$id'");
    }

    // ---------- DELETE USER ----------
    mysqli_query($conn, "DELETE FROM user WHERE UserID='$id'");

    echo "<script>
        alert('User account deleted successfully!');
        window.location='admin_user_list.php';
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View User</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Admin layout -->
    <link rel="stylesheet" href="../templates/admin_style.css">

    <style>
        /* OUTER CARD – light orange */
        .profile-card {
            background: #ffffff;
            border-left: 8px solid #FFB873;
            border-radius: 20px;
        }

        /* INNER CARD – white */
        .profile-inner {
            background: #ffffff;
            border-radius: 16px;
            padding: 25px;
        }

        .form-control, .form-select {
            background: #FFEEDB;
            border: 1px solid #FFB873;
            border-radius: 12px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #FF9A3C;
            box-shadow: none;
        }

        .btn-save {
            background: #FF9A3C;
            color: white;
            font-weight: 700;
            border-radius: 12px;
            padding: 12px 20px;
            border: none;
        }

        .btn-delete {
            background: #C40000;
            color: white;
            font-weight: 700;
            border-radius: 12px;
            padding: 12px 20px;
            border: none;
            margin-left: 10px;
        }
    </style>
</head>

<body class="bg-light">

<?php include '../templates/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="container mt-4">

        <div class="header mb-4">👤 View / Update User Profile</div>

        <!-- ORANGE BACKGROUND -->
        <div class="profile-card">

            <!-- WHITE INNER -->
            <div class="profile-inner">

                <form method="POST">

                    <input type="hidden" name="userid" value="<?= $user['UserID'] ?>">

                    <div class="mb-3">
                        <label><b>User ID</b></label>
                        <input type="text" class="form-control" value="<?= $user['UserID'] ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label><b>Name</b></label>
                        <input type="text" name="name" class="form-control" value="<?= $user['UserName'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label><b>Email</b></label>
                        <input type="email" name="email" class="form-control" value="<?= $user['UserEmail'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label><b>Password</b></label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                    </div>

                    <div class="mb-4">
                        <label><b>User Role</b></label>
                        <select name="role" class="form-select" required>
                            <option value="Administrator" <?= $user['UserRole']==='Administrator'?'selected':'' ?>>Administrator</option>
                            <option value="Student" <?= $user['UserRole']==='Student'?'selected':'' ?>>Student</option>
                            <option value="Security Staff" <?= $user['UserRole']==='Security Staff'?'selected':'' ?>>Security Staff</option>
                        </select>
                    </div>

                    <button type="submit" name="update" class="btn btn-save">
                        💾 Save Changes
                    </button>

                    <button type="submit"
                            name="delete"
                            class="btn btn-delete"
                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone!');">
                        🗑 Delete User
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>

</body>
</html>
