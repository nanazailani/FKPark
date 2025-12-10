<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Restrict to logged-in security staff only
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../Module1/login.php");
    exit();
}

$userID = $_SESSION['UserID'];

// Fetch existing security staff information
$result = mysqli_query($conn, "SELECT * FROM User WHERE UserID = '$userID'");
$user = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // ⚠️ If password empty, keep existing hashed password
    if (empty($password)) {
        $hashedPassword = $user['UserPassword'];
    } else {
        // Hash new password properly
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
            window.location='security_edit_profile.php?id=$userID';
        </script>";
    } else {
        echo 'ERROR: ' . mysqli_error($conn);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Security Profile</title>
    <link rel="stylesheet" href="../templates/security_style.css">

    <style>
        .edit-box {
            background: #ffffff;
            padding: 25px;
            border-radius: 20px;
            width: 70%;
            margin-top: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border-left: 8px solid #FFA74A;
        }

        .edit-box h2 {
            color: #6A3C00;
            margin-bottom: 15px;
            font-weight: 700;
        }

        label {
            font-weight: 600;
            color: #6A3C00;
        }

        input {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #FFCE8A;
            background: #FFF8E6;
            margin-bottom: 15px;
        }

        button {
            background: #FF9A3C;
            color: white;
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

<?php include '../templates/security_sidebar.php'; ?>

<div class="main-content">

    <div class="header">⚙️ Edit Profile</div>

    <div class="edit-box">

        <form method="POST">

            <label>Full Name</label>
            <input type="text" name="name" value="<?= $user['UserName']; ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= $user['UserEmail']; ?>" required>

            <label>Password (leave blank to keep current)</label>
            <input type="password" name="password" placeholder="Enter new password">

            <button type="submit">Save Changes</button>

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
