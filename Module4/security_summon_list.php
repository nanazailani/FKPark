<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../login.php");
    exit();
}

/* -----------------------------------------
   FIXED SQL QUERY
   - Removed invalid Student table join
   - Correct StudentID using Vehicle table
   - SummonStatus now displays properly
----------------------------------------- */

$sql = "
    SELECT 
        S.SummonID, 
        S.SummonDate, 
        S.SummonTime, 
        S.Location,
        S.SummonStatus,
        U.UserName AS StudentName,
        V.StudentID
    FROM Summon S
    LEFT JOIN Vehicle V ON S.VehicleID = V.VehicleID
    LEFT JOIN User U ON V.StudentID = U.UserID
    ORDER BY S.SummonID DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Summon List</title>

    <style>
        .action-btn {
            background: #FFE28A;
            color: #5A4B00;
            padding: 8px 18px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            border: 2px solid #F7D774;
            transition: 0.2s ease;
            display: inline-block;
        }

        .action-btn:hover {
            background: #FFD760;
            transform: scale(1.05);
            color: #5A4B00;
        }

        .action-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 13px;
            color: white;
            display: inline-block;
            text-transform: capitalize;
        }

        .status-badge.unpaid {
            background: #FF6B6B;
        }

        .status-badge.paid {
            background: #4CAF50;
        }

        .status-badge.cancelled {
            background: #FFC93C;
            color: #5A4B00;
        }

        .status-badge.rejected {
            background: #FF9800;
        }
    </style>

    <link rel="stylesheet" href="../templates/security_style.css">
</head>

<body>

    <!-- SIDEBAR -->
    <?php include '../templates/security_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <div class="header">⚠️ Summon List</div>

        <!-- SEARCH + FILTER BAR -->
        <div style="margin-bottom: 15px; display: flex; gap: 15px;">

            <input type="text" id="searchInput" placeholder="Search summons..."
                style="padding: 10px; width: 250px; border-radius: 10px; border: 1px solid #FFD972; background:#FFF9D7;">

            <select id="statusFilter"
                style="padding: 10px; border-radius: 10px; border:1px solid #FFD972; background:#FFF9D7;">
                <option value="">All Status</option>
                <option value="unpaid">Unpaid</option>
                <option value="paid">Paid</option>
            </select>

            <input type="date" id="dateFilter"
                style="padding: 10px; border-radius: 10px; border:1px solid #FFD972; background:#FFF9D7;">

        </div>

        <!-- SUMMON TABLE -->
        <div class="box">
            <h2>All Summons</h2>

            <table>
                <tr>
                    <th>Summon ID</th>
                    <th>Student ID</th>
                    <th>Student</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['SummonID'] ?></td>
                        <td><?= $row['StudentID'] ?></td>
                        <td><?= $row['StudentName'] ?></td>
                        <td><?= $row['SummonDate'] ?></td>
                        <td><?= $row['SummonTime'] ?></td>
                        <td><?= $row['Location'] ?></td>

                        <td>
                            <span class="status-badge <?= strtolower($row['SummonStatus']); ?>">
                                <?= $row['SummonStatus']; ?>
                            </span>
                        </td>

                        <td class="action-container">
                            <a class="action-btn" href="security_summon_view.php?id=<?= $row['SummonID'] ?>">
                                🔍 View
                            </a>

                            <a class="action-btn"
                                href="security_edit_summon.php?id=<?= $row['SummonID'] ?>"
                                style="background:#B9E6FF; border:2px solid #8AD4FF; color:#004466;">
                                ✏️ Edit
                            </a>

                            <a class="action-btn delete-btn"
                                data-id="<?= $row['SummonID'] ?>"
                                style="background:#FFB3B3; border:2px solid #FF8A8A; color:#660000;">
                                🗑 Delete
                            </a>
                        </td>

                    </tr>
                <?php endwhile; ?>

            </table>
        </div>

    </div>

    <!-- FILTER SCRIPT -->
    <script>
        function filterTable() {
            const search = document.getElementById("searchInput").value.toLowerCase();
            const status = document.getElementById("statusFilter").value.toLowerCase();
            const date = document.getElementById("dateFilter").value;

            const rows = document.querySelectorAll("table tr:not(:first-child)");

            rows.forEach(row => {
                const cells = row.querySelectorAll("td");

                const summonID = cells[0].innerText.toLowerCase();
                const studentName = cells[2].innerText.toLowerCase();
                const summonDate = cells[3].innerText;
                const summonStatus = cells[6].querySelector("span").classList[1];

                let visible = true;

                if (search && !summonID.includes(search) && !studentName.includes(search)) {
                    visible = false;
                }

                if (status && summonStatus !== status) {
                    visible = false;
                }

                if (date && summonDate !== date) {
                    visible = false;
                }

                row.style.display = visible ? "" : "none";
            });
        }

        document.getElementById("searchInput").addEventListener("keyup", filterTable);
        document.getElementById("statusFilter").addEventListener("change", filterTable);
        document.getElementById("dateFilter").addEventListener("change", filterTable);
    </script>

    <!-- DELETE CONFIRMATION POPUP -->
    <div id="deletePopup"
        style="
        display:none; 
        position:fixed; 
        top:0; left:0; 
        width:100%; height:100%;
        background:rgba(0,0,0,0.4); 
        z-index:9999; 
        justify-content:center; 
        align-items:center;">

        <div style="
        background:#FFF2C7; 
        padding:25px; 
        width:350px;
        border-radius:18px; 
        border:3px solid #FFD972;
        text-align:center;">

            <h3 style="color:#5A4B00;">⚠️ Confirm Delete</h3>

            <p style="color:#5A4B00;">Are you sure you want to delete this summon?</p>

            <div style="margin-top:20px; display:flex; justify-content:space-between;">
                <button id="cancelDelete"
                    style="padding:10px 20px; border:none; border-radius:10px; background:#EAEAEA; font-weight:bold;">
                    Cancel
                </button>

                <button id="confirmDelete"
                    style="padding:10px 22px; border-radius:10px; border:2px solid #FF6B6B; background:#FF8A8A; color:#660000; font-weight:bold;">
                    Delete
                </button>
            </div>

        </div>
    </div>

    <script>
        let deleteID = null;

        document.querySelectorAll(".delete-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                deleteID = btn.getAttribute("data-id");
                document.getElementById("deletePopup").style.display = "flex";
            });
        });

        document.getElementById("cancelDelete").addEventListener("click", () => {
            document.getElementById("deletePopup").style.display = "none";
        });

        document.getElementById("confirmDelete").addEventListener("click", () => {
            window.location.href = "security_delete_summon.php?id=" + deleteID;
        });
    </script>

</body>

</html>