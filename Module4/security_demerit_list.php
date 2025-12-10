<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Security Staff') {
    header("Location: ../login.php");
    exit();
}

// Get total demerit points per student
$sql = "
SELECT 
    U.UserID,   
    U.UserName,
    (
        SELECT SUM(VT.ViolationPoints)
        FROM Vehicle V2
        LEFT JOIN Summon S2 ON V2.VehicleID = S2.VehicleID
        LEFT JOIN ViolationType VT ON S2.ViolationTypeID = VT.ViolationTypeID
        WHERE V2.StudentID = U.UserID
    ) AS TotalPoints,
    
    (SELECT StartDate FROM PunishmentDuration 
        WHERE StudentID = U.UserID
        ORDER BY PunishmentDurationID DESC LIMIT 1) AS StartDate,

    (SELECT EndDate FROM PunishmentDuration 
        WHERE StudentID = U.UserID
        ORDER BY PunishmentDurationID DESC LIMIT 1) AS EndDate,

    (SELECT Status FROM PunishmentDuration
        WHERE StudentID = U.UserID
        ORDER BY PunishmentDurationID DESC LIMIT 1) AS PunishmentStatus
    
FROM User U
WHERE U.UserRole = 'Student'
ORDER BY TotalPoints DESC;
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Demerit Records</title>
    <link rel="stylesheet" href="../templates/security_style.css">
</head>

<body>

    <!-- Sidebar -->
    <?php include '../templates/security_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <div class="header">📉 Demerit Records</div>

        <!-- FILTER BAR -->
        <div style="margin-bottom: 15px; display: flex; gap: 15px;">

            <!-- Search bar -->
            <input
                type="text"
                id="searchInput"
                placeholder="Search Student ID"
                style="padding: 10px; width: 250px; border-radius: 10px; border: 1px solid #FFD972; background:#FFF9D7;">

            <!-- Punishment filter -->
            <select
                id="punishmentFilter"
                style="padding: 10px; border-radius: 10px; border:1px solid #FFD972; background:#FFF9D7;">
                <option value="">All Punishment</option>
                <option value="warning">Warning</option>
                <option value="1 semester">1 Semester</option>
                <option value="2 semesters">2 Semesters</option>
                <option value="entire">Entire Duration</option>
            </select>

            <!-- Sort dropdown -->
            <select
                id="sortPoints"
                style="padding: 10px; border-radius: 10px; border:1px solid #FFD972; background:#FFF9D7;">
                <option value="desc">Sort: Highest → Lowest</option>
                <option value="asc">Sort: Lowest → Highest</option>
            </select>

        </div>

        <div class="box">
            <h2>Student Demerit Points</h2>

            <table id="demeritTable">
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th class="center">Total Points</th>
                    <th class="center">Punishment</th>
                    <th class="center">Start Date</th>
                    <th class="center">End Date</th>
                    <th class="center">Status</th>
                </tr>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <?php
                    $points = $row['TotalPoints'] ?? 0;

                    if ($points < 20) {
                        $punishment = "⚠️ Warning";
                        $punishmentRaw = "warning";
                    } else if ($points < 50) {
                        $punishment = "⛔ Vehicle Revoked (1 Semester)";
                        $punishmentRaw = "1 semester";
                    } else if ($points < 80) {
                        $punishment = "⛔ Vehicle Revoked (2 Semesters)";
                        $punishmentRaw = "2 semesters";
                    } else {
                        $punishment = "❌ Banned Entire Study Duration";
                        $punishmentRaw = "entire";
                    }
                    ?>

                    <tr>
                        <td><?= $row['UserID']; ?></td>
                        <td><?= $row['UserName']; ?></td>
                        <td class="center"><?= $points; ?></td>
                        <td class="center" data-status="<?= $punishmentRaw ?>">
                            <?= $punishment; ?>
                        </td>
                        <td class="center"><?= $row['StartDate'] ?? '-' ?></td>
                        <td class="center"><?= $row['EndDate'] ?? '-' ?></td>
                        <td class="center">
                            <?php if ($row['PunishmentStatus'] == 'Active'): ?>
                                <span style="background:#FF6B6B; color:white; padding:6px 18px; border-radius:25px; font-weight:700; display:inline-block;">
                                    Active
                                </span>
                            <?php elseif ($row['PunishmentStatus'] == 'Completed'): ?>
                                <span style="background:#4CAF50; color:white; padding:6px 18px; border-radius:25px; font-weight:700; display:inline-block;">
                                    Completed
                                </span>
                            <?php else: ?>
                                <span style="background:#BDBDBD; color:white; padding:6px 18px; border-radius:25px; font-weight:700; display:inline-block;">
                                    None
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endwhile; ?>

            </table>
        </div>

    </div>

    <script>
        function filterAndSortTable() {
            const search = document.getElementById("searchInput").value.toLowerCase();
            const punishment = document.getElementById("punishmentFilter").value.toLowerCase();
            const sortValue = document.getElementById("sortPoints").value;

            const table = document.getElementById("demeritTable");
            let rows = Array.from(table.querySelectorAll("tr:not(:first-child)"));

            // FILTER
            rows.forEach(row => {
                const studentID = row.cells[0].innerText.toLowerCase();
                const statusRaw = row.cells[3].getAttribute("data-status");

                let show = true;

                if (search && !studentID.includes(search)) show = false;
                if (punishment && statusRaw !== punishment) show = false;

                row.style.display = show ? "" : "none";
            });

            // SORT (ASC / DESC)
            rows = rows.filter(row => row.style.display !== "none");

            rows.sort((a, b) => {
                const pointsA = parseInt(a.cells[2].innerText);
                const pointsB = parseInt(b.cells[2].innerText);
                return sortValue === "asc" ? pointsA - pointsB : pointsB - pointsA;
            });

            rows.forEach(row => table.appendChild(row));
        }

        document.getElementById("searchInput").addEventListener("keyup", filterAndSortTable);
        document.getElementById("punishmentFilter").addEventListener("change", filterAndSortTable);
        document.getElementById("sortPoints").addEventListener("change", filterAndSortTable);
    </script>

</body>

</html>