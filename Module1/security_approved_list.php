<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Allow only Security Staff
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../Module1/login.php");
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approved Vehicle List</title>

    <!-- ✅ BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Your security layout -->
    <link rel="stylesheet" href="../templates/security_style.css">

    <style>
        /* SEARCH */
        .search-box input {
            border-radius: 10px;
            border: 1px solid #FFD972;
            background: #FFF9D7;
        }

        .search-box button {
            border-radius: 10px;
            background: #FFE28A;
            border: 2px solid #FFD972;
            font-weight: 700;
            color: #5A4B00;
        }

        /* BOX */
        .box {
            background: #FFFFFF;
            padding: 25px;
            border-radius: 20px;
            border-left: 8px solid #FFD972;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        /* ✅ BOOTSTRAP TABLE OVERRIDES */
        .table thead th {
            background: #FFE9A7;   /* YELLOW HEADER */
            color: #5A4B00;
            font-weight: 700;
            border-bottom: none;
        }

        .table tbody td {
            border-color: #F7EBC6;
        }

        .table tbody tr:hover {
            background: #FFF8D8;
        }

        .no-data {
            padding: 20px;
            background: #FFF2C7;
            border-left: 8px solid #FFD972;
            border-radius: 15px;
            font-weight: 700;
            color: #5A4B00;
        }
        .search-input {
            max-width: 320px;   /* <-- controls the size */
        }

    </style>
</head>

<body>

<?php include "../templates/security_sidebar.php"; ?>

<div class="main-content">

    <div class="header">✅ Approved Vehicle List</div>

    <!-- SEARCH -->
    <form method="GET" class="search-box d-flex gap-2 mb-3">
    <input 
        type="text"
        name="search"
        class="form-control form-control-sm search-input"
        placeholder="Search plate number or staff ID"
        value="<?= htmlspecialchars($search) ?>"
    >
    <button type="submit" class="btn btn-sm btn-warning fw-bold">
        🔍 Search
    </button>
    </form>


    <?php if (mysqli_num_rows($result) == 0): ?>
        <div class="no-data">No approved vehicles found.</div>
    <?php else: ?>

        <div class="box table-responsive">
            <table class="table text-center align-middle">
                <thead>
                    <tr>
                        <th>Plate Number</th>
                        <th>Vehicle Type</th>
                        <th>Owner</th>
                        <th>Staff ID</th>
                        <th>Approved By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['PlateNumber']) ?></td>
                            <td><?= htmlspecialchars($row['VehicleType']) ?></td>
                            <td><?= htmlspecialchars($row['OwnerName']) ?></td>
                            <td><?= htmlspecialchars($row['StaffID']) ?></td>
                            <td><?= htmlspecialchars($row['ApprovedBy']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>

<script>
    window.addEventListener("pageshow", function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>

</body>
</html>
