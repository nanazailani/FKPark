<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Administrator') {
    header("Location: ../Module1/login.php");
    exit();
}

// Handle submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $studentID = mysqli_real_escape_string($conn, $_POST['studentID']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $program = mysqli_real_escape_string($conn, $_POST['program']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);

    // 🔐 Encrypt the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into User table
    $sqlUser = "
        INSERT INTO user (UserID, UserName, UserEmail, UserPassword, UserRole)
        VALUES ('$studentID', '$name', '$email', '$hashedPassword', 'Student')
    ";

    if (!mysqli_query($conn, $sqlUser)) {
        die("ERROR inserting into User: " . mysqli_error($conn));
    }

    // Insert into Student table
    $sqlStudent = "
        INSERT INTO student (UserID, StudentYear, StudentProgram)
        VALUES ('$studentID', '$year', '$program')
    ";

    mysqli_query($conn, $sqlStudent);

    echo "<script>
        alert('Student Registered Successfully!');
        window.location='../Module1/admin_user_registration.php';
    </script>";
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>User Registration</title>
    <link rel="stylesheet" href="../templates/admin_style.css">

    <style>
        .form-box {
            background: #ffffff;
            padding: 25px;
            border-radius: 20px;
            width: 70%;
            margin-top: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border-left: 8px solid #FFB873;
        }

        .form-box h2 {
            color: #773f00;
            margin-bottom: 15px;
            font-weight: 700;
        }

        label {
            font-weight: 600;
            color: #773f00;
        }

        input {
            width: 95%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #FFB873;
            background: #FFEEDB;
            margin-bottom: 15px;
        }

        select {
            width: 50%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #FFB873;
            background: #FFEEDB;
            margin-bottom: 15px;
        }

        button {
            background: #FF9A3C;
            color: #fff;
            padding: 12px 22px;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
        }

        button:hover {
            background: #FF7F11;
            transform: scale(1.03);
        }
    </style>
</head>

<body>

    <?php include '../templates/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="header">🧑‍🎓 Student Registration</div>

        <div class="form-box">

            <form method="POST">

                <label>Student ID</label>
                <input type="text" name="studentID" required>

                <label>Full Name</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>Program</label>
                <input type="text" name="program" placeholder="Example: Software Engineering" required>

                <label>Year</label>
                <br>
                <select name="year" required>
                    <option value="">Select Year</option>
                    <option value="Year 1">Year 1</option>
                    <option value="Year 2">Year 2</option>
                    <option value="Year 3">Year 3</option>
                    <option value="Year 4">Year 4</option>
                </select>
                <br>
                <button type="submit">Register Student</button>

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