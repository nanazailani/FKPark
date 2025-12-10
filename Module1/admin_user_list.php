<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Administrator') {
    header("Location: ../Module1/login.php");
    exit();
}

// Filter role
$filter = isset($_GET['role']) ? $_GET['role'] : "All";

// Build query
if ($filter == "All") {
    $query = "SELECT * FROM User ORDER BY UserName ASC";
} else {
    $query = "SELECT * FROM User WHERE UserRole = '$filter' ORDER BY UserName ASC";
}

$users = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>User List</title>
    <link rel="stylesheet" href="../templates/admin_style.css">

    <style>
        .user-box {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            width: 70%;
            border-left: 8px solid #FFB873;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #FFE2C8;
            font-size: 16px;
        }

        .user-avatar {
            font-size: 24px;
            margin-right: 10px;
        }

        .view-btn {
            background: #FF9A3C;
            padding: 8px 15px;
            color: white;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
            font-weight: 700;
        }

        .view-btn:hover {
            background: #FF7F11;
        }

        .filter-box {
            margin-bottom: 20px;
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
            margin-left: 5px;
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
<body>

<?php include '../templates/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="header">👥 User List</div>

    <div class="filter-box">
        <form method="GET">
            <select name="role" class="filter-select">
                <option value="All" <?= $filter == "All" ? 'selected' : '' ?>>All Users</option>
                <option value="Administrator" <?= $filter == "Administrator" ? 'selected' : '' ?>>Administrator</option>
                <option value="Student" <?= $filter == "Student" ? 'selected' : '' ?>>Student</option>
                <option value="Security Staff" <?= $filter == "Security Staff" ? 'selected' : '' ?>>Security Staff</option>
            </select>

            <button type="submit" class="filter-btn">Filter</button>
        </form>
    </div>

    <div class="user-box">

        <?php while ($u = mysqli_fetch_assoc($users)): ?>
            <div class="user-item">
                <div>
                    <span class="user-avatar">👨🏻‍💼</span>
                    <?= $u['UserName'] ?> (<?= $u['UserRole'] ?>)
                </div>
                
                <a class="view-btn" href="admin_user_view.php?id=<?= $u['UserID'] ?>">View</a>
            </div>
        <?php endwhile; ?>

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
