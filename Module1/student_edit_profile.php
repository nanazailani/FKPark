<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Restrict to logged-in students only
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header("Location: ../Module1/login.php");
    exit();
}

$userID = $_SESSION['UserID'];

// Fetch existing student information
$result = mysqli_query($conn, "SELECT * FROM User WHERE UserID = '$userID'");
$user = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // If password empty → keep old hashed password
    if (empty($password)) {
        $hashedPassword = $user['UserPassword'];
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql = "
        UPDATE User 
        SET UserName = '$name',
            UserEmail = '$email',
            UserPassword = '$hashedPassword'
        WHERE UserID = '$userID'
    ";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
            alert('Profile updated successfully!');
            window.location='student_edit_profile.php?id=$userID';
        </script>";
    } else {
        echo 'ERROR: ' . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student Profile</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Student layout -->
    <link rel="stylesheet" href="../templates/student_style.css">

    <style>
        /* OUTER CARD – light blue background */
        .profile-card {
            background: #EAF4FF;
            border-left: 8px solid #5B9BFF;
            border-radius: 20px;
            padding: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .form-control {
            background: #F0F8FF;
            border: 1px solid #82C1FF;
            border-radius: 12px;
            padding: 12px;
        }

        .form-control:focus {
            border-color: #1B98E0;
            box-shadow: none;
        }

        .btn-custom {
            background: #1B98E0;
            color: white;
            padding: 12px 22px;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
        }

        .btn-custom:hover {
            background: #1478B5;
            transform: scale(1.03);
        }
    </style>
</head>

<body class="bg-light">

<?php include '../templates/student_sidebar.php'; ?>

<div class="main-content">

    <!-- SAME STRUCTURE AS ADMIN & SECURITY -->
    <div class="container mt-4">

        <div class="header mb-4">⚙️ Edit Profile</div>

        <!-- BLUE BACKGROUND CARD -->
        <div class="card profile-card">

            <!-- WHITE INNER CARD -->
            <div class="card-body">

                <form method="POST">

                    <div class="mb-3">
                            <label><b>Full Name</b></label>
                            <input class="form-control" type="text" name="name" value="<?= $user['UserName']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label><b>Email</b></label>
                            <input class="form-control" type="email" name="email" value="<?= $user['UserEmail']; ?>" required>
                        </div>

                        <div class="mb-4">
                            <label><b>Password (leave blank to keep current)</b></label>
                            <input class="form-control" type="password" name="password" placeholder="Enter new password">
                        </div>

                    <button type="submit" class="btn btn-custom">
                        Save Changes
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
