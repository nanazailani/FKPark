<?php
//start session php 
session_start();

//no cache - no go back after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

//connect database
require_once '../config.php';

//restrict access to administrator only
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Administrator') {
    header("Location: ../Module1/login.php");
    exit();
}

//get logged-in admin info
$userID = $_SESSION['UserID'];
$result = mysqli_query($conn, "SELECT * FROM User WHERE UserID = '$userID'");
$user = mysqli_fetch_assoc($result);

//update profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    //if password empty, keep old password
    if (empty($password)) {
        $hashedPassword = $user['UserPassword'];
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    }

    // update admin profile
    $sql = "
        UPDATE User
        SET UserName = '$name',
            UserEmail = '$email',
            UserPassword = '$hashedPassword'
        WHERE UserID = '$userID'
    ";

    //display message
    if (mysqli_query($conn, $sql)) 
    {
        echo "
        <script>
            alert('Profile updated successfully!');
            window.location='admin_edit_profile.php?id=$userID';
        </script>";
    } else 
    {
        echo 'ERROR: ' . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Edit Admin Profile</title>
        <!--bootstrap-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../templates/admin_style.css">

        <style>
            .profile-card {
                background: #ffffff;
                border-left: 8px solid #FFB873;
                border-radius: 20px;
            }

            .form-control {
                background: #FFEEDB;
                border: 1px solid #FFB873;
                border-radius: 14px;
            }

            .form-control:focus {
                border-color: #FF9A3C;
                box-shadow: none;
            }

            .btn-custom {
                background: #FF9A3C;
                color: #fff;
                font-weight: 700;
                border-radius: 12px;
                padding: 10px 22px;
                border: none;
            }

            .btn-custom:hover {
                background: #FF7F11;
            }
        </style>
    </head>

    <body class="bg-light">
    
    <?php include_once('../templates/admin_sidebar.php'); ?>

    <div class="main-content">

        <div class="container mt-4">

            <div class="header mb-4">
                ⚙️ Edit Profile
            </div>

            <div class="card profile-card">
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

                        <button type="submit" class="btn btn-custom">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

        <script>
            //prevent access via browser back button after logout
            window.addEventListener("pageshow", function (event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        </script>
    </body>
</html>
