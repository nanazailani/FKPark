<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Allow only Security Staff
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../index.php");
    exit();
}

// ======================= SEARCH LOGIC =========================
$search = '';
$whereClause = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $whereClause = "
        AND (
            V.PlateNumber LIKE '%$search%'
            OR A.UserID LIKE '%$search%'
        )
    ";
}

// ======================= GET APPROVED VEHICLES =========================
$sql = "
    SELECT 
        V.PlateNumber,
        V.VehicleType,
        O.UserName AS OwnerName,
        A.UserID   AS StaffID,
        A.UserName AS ApprovedBy
    FROM vehicle V
    JOIN user O ON V.UserID = O.UserID
    JOIN user A ON V.ApprovedBy = A.UserID
    WHERE V.ApprovalStatus = 'Approved'
      AND A.UserRole = 'Security Staff'
      $whereClause
    ORDER BY V.VehicleID DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Approved Vehicle List</title>
    <link rel="stylesheet" href="../templates/security_style.css">

    <style>
        .search-box input {
            padding: 10px;
            width: 260px;
            border-radius: 10px;
            border: 1px solid #FFD972;
            background: #FFF9D7;
        }

        .search-box button {
            padding: 10px 18px;
            border-radius: 10px;
            border: 2px solid #FFD972;
            background: #FFE28A;
            font-weight: 700;
            color: #5A4B00;
            cursor: pointer;
        }

        .box {
            background: #FFFFFF;
            padding: 25px;
            border-radius: 20px;
            border-left: 8px solid #FFD972;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }

        th,
        td {
            text-align: center;
        }


        .no-data {
            padding: 20px;
            background: #FFF2C7;
            border-left: 8px solid #FFD972;
            border-radius: 15px;
            font-weight: 700;
            color: #5A4B00;
        }
    </style>
</head>

<body>

    <?php include "../templates/security_sidebar.php"; ?>

    <div class="main-content">

        <div class="header">✅ Approved Vehicle List</div>

        <!-- SEARCH -->
        <form method="GET" class="search-box" style="margin-bottom:15px;">
            <input
                type="text"
                name="search"
                placeholder="Search plate number or staff ID"
                value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search 🔍</button>
        </form>

        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="no-data">No approved vehicles found.</div>
        <?php else: ?>

            <div class="box">
                <table>
                    <tr>
                        <th>Plate Number</th>
                        <th>Vehicle Type</th>
                        <th>Owner</th>
                        <th>Staff ID</th>
                        <th>Approved By</th>
                    </tr>

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['PlateNumber']) ?></td>
                            <td><?= htmlspecialchars($row['VehicleType']) ?></td>
                            <td><?= htmlspecialchars($row['OwnerName']) ?></td>
                            <td><?= htmlspecialchars($row['StaffID']) ?></td>
                            <td><?= htmlspecialchars($row['ApprovedBy']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

        <?php endif; ?>

    </div>

    <script>
        //pageshow - event bila page show. e.g - tekan background
        window.addEventListener("pageshow", function(event) {
            //true kalau the page is cached 
            if (event.persisted) {
                //page reload
                window.location.reload();
            }
        });
    </script>

</body>

</html>