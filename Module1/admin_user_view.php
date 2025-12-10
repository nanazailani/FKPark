<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Restrict to admin only
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Administrator') {
    header("Location: ../Module1/login.php");
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
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update = "
        UPDATE User
        SET UserName='$name',
            UserEmail='$email',
            UserPassword='$password',
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

    // Detect the user role
    $roleQuery = mysqli_query($conn, "SELECT UserRole FROM user WHERE UserID='$id'");
    $roleData = mysqli_fetch_assoc($roleQuery);
    $role = $roleData['UserRole'];


    // ============================
    // IF STUDENT
    // ============================
    if ($role == "Student") {

        // 1️⃣ BOOKING MODULE
        mysqli_query($conn, "DELETE FROM bookingqrcode 
             WHERE BookingID IN (SELECT BookingID FROM booking WHERE StudentID='$id')");
        mysqli_query($conn, "DELETE FROM parkinglog WHERE StudentID='$id'");
        mysqli_query($conn, "DELETE FROM booking WHERE StudentID='$id'");


        // 2️⃣ SUMMON MODULE (FK-safe)
        $summonIDs = [];
        $getSummons = mysqli_query($conn, "
            SELECT SummonID 
            FROM summon 
            WHERE VehicleID IN (SELECT VehicleID FROM vehicle WHERE StudentID='$id')
        ");

        while ($row = mysqli_fetch_assoc($getSummons)) {
            $summonIDs[] = $row['SummonID'];
        }

        if (!empty($summonIDs)) {

            $ids = implode(",", $summonIDs);

            // (a) Delete summonqrcode
            mysqli_query($conn, "DELETE FROM summonqrcode WHERE SummonID IN ($ids)");

            // (b) Delete demerit linked to these summons
            mysqli_query($conn, "DELETE FROM demerit WHERE SummonID IN ($ids)");

            // (c) Delete remaining demerit linked to student
            mysqli_query($conn, "DELETE FROM demerit WHERE StudentID='$id'");

            // (d) Delete summons
            mysqli_query($conn, "DELETE FROM summon WHERE SummonID IN ($ids)");
        }


        // 3️⃣ PUNISHMENT
        mysqli_query($conn, "DELETE FROM punishmentduration WHERE StudentID='$id'");

        // 4️⃣ VEHICLES
        mysqli_query($conn, "DELETE FROM vehicle WHERE StudentID='$id'");

        // 5️⃣ STUDENT PROFILE
        mysqli_query($conn, "DELETE FROM student WHERE StudentID='$id'");
    }




    // ============================
    // IF SECURITY STAFF
    // ============================
    if ($role == "Security Staff") {

        // Get all summons issued by this staff
        $summonIDs = [];
        $getSummons = mysqli_query($conn, "
            SELECT SummonID FROM summon WHERE SStaffID='$id'
        ");

        while ($row = mysqli_fetch_assoc($getSummons)) {
            $summonIDs[] = $row['SummonID'];
        }

        if (!empty($summonIDs)) {

            $ids = implode(",", $summonIDs);

            // 1️⃣ Delete summonqrcode
            mysqli_query($conn, "DELETE FROM summonqrcode WHERE SummonID IN ($ids)");

            // 2️⃣ Delete demerit linked to these summons
            mysqli_query($conn, "DELETE FROM demerit WHERE SummonID IN ($ids)");

            // 3️⃣ Delete summons
            mysqli_query($conn, "DELETE FROM summon WHERE SummonID IN ($ids)");
        }

        // 4️⃣ Delete vehicles approved by staff
        mysqli_query($conn, "DELETE FROM vehicle WHERE SStaffID='$id'");

        // 5️⃣ Delete securitystaff profile
        mysqli_query($conn, "DELETE FROM securitystaff WHERE UserID='$id'");
    }



    // ============================
    // DELETE USER ACCOUNT
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
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        label {
            font-weight: 600;
            color: #773f00;
        }

        input, select {
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
            <input type="text" name="password" value="<?= $user['UserPassword'] ?>" required>

            <label>User Role</label>
            <input type="text" value="<?= $user['UserRole'] ?>" disabled>

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
    window.addEventListener("pageshow", function (event) {
        if (event.persisted) {
            // Force a full reload
            window.location.reload();
        }
    });
</script>
</body>
</html>