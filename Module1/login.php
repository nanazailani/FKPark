<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $userType = mysqli_real_escape_string($conn, $_POST['userType']);

    // Step 1: Get the user by ID + Role
    $sql = "SELECT * FROM user WHERE UserID = '$userID' AND UserRole = '$userType'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {

        $row = mysqli_fetch_assoc($result);

        // ✅ CHECK PASSWORD FIRST
        if (password_verify($password, $row['UserPassword'])) {

            // ✅ SET SESSION
            $_SESSION['UserID']   = $row['UserID'];
            $_SESSION['UserName'] = $row['UserName'];
            $_SESSION['UserRole'] = $row['UserRole'];

            // ✅ REDIRECT LOGIC (QR or dashboard)
            if (isset($_SESSION['redirect_after_login'])) {

                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirect);
            } else {

                if ($row['UserRole'] == "Student") {
                    header("Location: ../Module1/student_dashboard.php");
                } else if ($row['UserRole'] == "Security Staff") {
                    header("Location: ../Module4/security_dashboard.php");
                } else if ($row['UserRole'] == "Administrator") {
                    header("Location: ../Module2/admin_dashboard.php");
                }
            }
            exit();
        } else {
            echo "<script>alert('Incorrect Password!'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('User Not Found or Wrong User Type!'); window.location='login.php';</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FKPark Login</title>

    <style>
        body {
            background-image: url("images/fkom.jpg");
            background-size: cover;
            font-family: Arial, sans-serif;
            padding-top: 80px;
        }

        .login-container {
            width: 350px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.2);
        }

        .login-container h2 {
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        * {
            box-sizing: border-box;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #aaa;
            border-radius: 5px;
        }


        .btn {
            width: 100%;
            background: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background: #0056b3;
        }

        .logo {
            display: block;
            margin: 0 auto;
            width: 150px;
            height: auto;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <img src="images/logoumpsa.png" alt="logoumpsa" class="logo">
        <h2>FKPark Login</h2>

        <form action="" method="POST">

            <div class="form-group">
                <label>User ID</label>
                <input type="text" name="userID" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>User Type</label>
                <select name="userType" required>
                    <option value="">-- Select --</option>
                    <option value="Administrator">Administrator</option>
                    <option value="Student">Student</option>
                    <option value="Security Staff">Security Staff</option>
                </select>
            </div>

            <button type="submit" class="btn"><b>LOGIN</b></button>

        </form>
    </div>

</body>

</html>