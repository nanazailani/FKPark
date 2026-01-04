<?php
// PHP to report all errors and warnings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// start session
session_start();

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// database connection
require_once '../config.php';

// check if admin
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Administrator') {
    header("Location: ../Module1/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $studentID = mysqli_real_escape_string($conn, $_POST['studentID']);
    $name      = mysqli_real_escape_string($conn, $_POST['name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $password  = mysqli_real_escape_string($conn, $_POST['password']);
    $program   = mysqli_real_escape_string($conn, $_POST['program']);
    $year      = mysqli_real_escape_string($conn, $_POST['year']);

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sqlUser = "
        INSERT INTO user (UserID, UserName, UserEmail, UserPassword, UserRole)
        VALUES ('$studentID', '$name', '$email', '$hashedPassword', 'Student')
    ";

    if (!mysqli_query($conn, $sqlUser)) {
        die("ERROR inserting into User: " . mysqli_error($conn));
    }

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Admin layout -->
    <link rel="stylesheet" href="../templates/admin_style.css">

    <style>
        .form-card {
            background: #ffffff;
            border-left: 8px solid #FFB873;
            border-radius: 20px;
        }

        .form-inner {
            background: #ffffff;
            border-radius: 16px;
            padding: 25px;
        }

        .form-control,
        .form-select {
            background: #FFEEDB;
            border: 1px solid #FFB873;
            border-radius: 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #FF9A3C;
            box-shadow: none;
        }

        .btn-custom {
            background: #FF9A3C;
            color: white;
            padding: 12px 22px;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
        }

        .btn-custom:hover {
            background: #FF7F11;
            transform: scale(1.03);
        }
    </style>
</head>

<body class="bg-light">

<?php include '../templates/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="container mt-4">

        <div class="header mb-4">🧑‍🎓 Student Registration</div>

        <div class="form-card">
            <div class="form-inner">

                <form name="register" method="POST">

                    <div class="mb-3">
                        <label><b>Student ID</b></label>
                        <!-- 🔧 FIX IS HERE -->
                        <input class="form-control"
                               type="text"
                               name="studentID"
                               onblur="myFunc()"
                               required>
                    </div>

                    <div class="mb-3">
                        <label><b>Full Name</b></label>
                        <input class="form-control" type="text" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label><b>Email</b></label>
                        <input class="form-control" type="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label><b>Password</b></label>
                        <input class="form-control" type="password" name="password" required>
                    </div>

                    <div class="mb-4">
                        <label><b>Year</b></label>
                        <select name="year" class="form-select" required>
                            <option value="">Select Year</option>
                            <option value="Year 1">Year 1</option>
                            <option value="Year 2">Year 2</option>
                            <option value="Year 3">Year 3</option>
                            <option value="Year 4">Year 4</option>
                        </select>
                    </div>

                    <!-- hidden program -->
                    <input type="hidden" name="program" id="program">

                    <button type="submit" class="btn btn-custom">
                        Register Student
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
function myFunc() {
    let id = document.register.studentID.value;
    let code = id.slice(0, 2).toUpperCase();
    let program = "";

    if (code === "CA") {
        program = "Computer Systems & Networking";
    } else if (code === "CB") {
        program = "Software Engineering";
    } else if (code === "CD") {
        program = "Multimedia Software";
    } else if (code === "CF") {
        program = "Cyber Security";
    } else if (code === "RC") {
        program = "Diploma";
    } else {
        program ="Not FKOM Student";
    }

    document.getElementById("program").value = program;
}
</script>

<script>
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>

</body>
</html>
