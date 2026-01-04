<?php
// start session
session_start();

// no cache - no go back after logout
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

// filter role
$filter = isset($_GET['role']) ? $_GET['role'] : "All";

// sql query
if ($filter == "All") {
    $query = "SELECT * FROM User ORDER BY UserName ASC";
} else {
    $query = "SELECT * FROM User WHERE UserRole = '$filter' ORDER BY UserName ASC";
}

// run sql
$users = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User List</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../templates/admin_style.css">

    <style>
        .user-card {
            background: #ffffff;
            border-left: 8px solid #FFB873;
            border-radius: 20px;
        }

        .user-inner {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
        }

        .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #FFE2C8;
            font-size: 16px;
        }

        .user-item:last-child {
            border-bottom: none;
        }

        .user-avatar {
            font-size: 22px;
            margin-right: 10px;
        }

        .view-btn {
            background: #FF9A3C;
            padding: 8px 16px;
            color: white;
            border-radius: 10px;
            font-size: 14px;
            text-decoration: none;
            font-weight: 700;
        }

        .view-btn:hover {
            background: #FF7F11;
        }

        .filter-select {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #FFB873;
            background: #FFEEDB;
            font-weight: 600;
            color: #773f00;
        }

        .filter-btn {
            background: #FF9A3C;
            padding: 10px 18px;
            margin-left: 8px;
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        .filter-btn:hover {
            background: #FF7F11;
        }
    </style>
</head>

<body class="bg-light">

<?php include '../templates/admin_sidebar.php'; ?>

<div class="main-content">

    <div class="container mt-4">

        <div class="header mb-4">👥 User List</div>

        <!-- FILTER -->
        <form method="GET" class="mb-3">
            <select name="role" class="filter-select">
                <option value="All" <?= $filter == "All" ? 'selected' : '' ?>>All Users</option>
                <option value="Administrator" <?= $filter == "Administrator" ? 'selected' : '' ?>>Administrator</option>
                <option value="Student" <?= $filter == "Student" ? 'selected' : '' ?>>Student</option>
                <option value="Security Staff" <?= $filter == "Security Staff" ? 'selected' : '' ?>>Security Staff</option>
            </select>

            <button type="submit" class="filter-btn">Filter</button>
        </form>

        <div class="user-card">

            <div class="user-inner">

                <?php while ($u = mysqli_fetch_assoc($users)): ?>
                    <div class="user-item">
                        <div>
                            <span class="user-avatar">👨🏻‍💼</span>
                            <?= $u['UserName'] ?> (<?= $u['UserRole'] ?>)
                        </div>

                        <a class="view-btn" href="admin_user_view.php?id=<?= $u['UserID'] ?>">
                            View
                        </a>
                    </div>
                <?php endwhile; ?>

            </div>
        </div>

    </div>
</div>

<script>
    // reload if press back button
    window.addEventListener("pageshow", function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>

</body>
</html>
