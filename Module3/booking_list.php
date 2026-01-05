<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Only allow Students
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header("Location: ../index.php");
    exit();
}

$studentID = $_SESSION['UserID'];

// ================================
// RETRIEVE USER'S BOOKINGS + ACTIVE LOG
// ================================
$sql = "
    SELECT 
        b.*,
        ps.SpaceCode,
        pa.AreaName,
        l.LogID
    FROM booking b
    JOIN parking_space ps 
        ON b.ParkingSpaceID = ps.ParkingSpaceID
    JOIN parking_area pa 
        ON ps.ParkingAreaID = pa.ParkingAreaID
    LEFT JOIN parkinglog l 
        ON b.BookingID = l.BookingID 
        AND l.CheckOutTime IS NULL
    WHERE b.UserID = '$studentID'
    ORDER BY 
        FIELD(b.Status, 'Pending', 'Active', 'Completed', 'Cancelled'),
        b.BookingID DESC
";

$bookings = mysqli_query($conn, $sql);

// Show SQL error if query fails
if (!$bookings) {
    die("SQL ERROR: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Bookings</title>

    <link rel="stylesheet" href="../templates/student_style.css">
    <link rel="stylesheet" href="_Module3CSS.css">
</head>

<body>

    <?php include '../templates/student_sidebar.php'; ?>

    <div class="main-content">

        <div class="header">📋 My Parking Bookings</div>

        <div class="list-box">

            <!-- FILTER BUTTONS -->
            <div class="filter-buttons">
                <button class="filter-btn active" onclick="filterTable('all')">All</button>
                <button class="filter-btn" onclick="filterTable('Pending')">Pending</button>
                <button class="filter-btn" onclick="filterTable('Active')">Active</button>
                <button class="filter-btn" onclick="filterTable('Completed')">Completed</button>
                <button class="filter-btn" onclick="filterTable('Cancelled')">Cancelled</button>
            </div>

            <table id="bookingTable">
                <tr>
                    <th>Booking ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Space</th>
                    <th>Status</th>
                    <th>QR Code</th>
                    <th>Action</th>
                </tr>

                <?php while ($row = mysqli_fetch_assoc($bookings)): ?>
                    <tr data-status="<?= $row['Status'] ?>">

                        <td><?= $row['BookingID'] ?></td>
                        <td><?= $row['BookingDate'] ?></td>
                        <td><?= $row['StartTime'] ?> - <?= $row['EndTime'] ?></td>
                        <td><?= $row['AreaName'] ?> - <?= $row['SpaceCode'] ?></td>

                        <td>
                            <?php if ($row['Status'] == 'Pending'): ?>
                                <span class="badge pending">Pending</span>
                            <?php elseif ($row['Status'] == 'Active'): ?>
                                <span class="badge active">Active</span>
                            <?php elseif ($row['Status'] == 'Completed'): ?>
                                <span class="badge completed">Completed</span>
                            <?php else: ?>
                                <span class="badge cancelled">Cancelled</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($row['Status'] == 'Pending'): ?>
                                <a href="view_detail.php?id=<?= $row['BookingID'] ?>" class="btn-viewqr">
                                    View QR
                                </a>
                            <?php else: ?>
                                <span style="color:gray;">N/A</span>
                            <?php endif; ?>
                        </td>


                        <td>
                            <?php if ($row['Status'] == 'Pending'): ?>
                                <div class="action-buttons">
                                    <a href="edit_booking.php?id=<?= $row['BookingID'] ?>" class="btn-edit">Edit</a>
                                    <a href="cancel_booking.php?id=<?= $row['BookingID'] ?>"
                                        class="btn-cancel"
                                        onclick="return confirm('Are you sure you want to cancel this booking?');">
                                        Cancel
                                    </a>
                                </div>
                            <?php elseif ($row['Status'] == 'Active' && !empty($row['LogID'])): ?>
                                <a href="checkout.php?log=<?= $row['LogID'] ?>"
                                    class="btn-viewqr"
                                    onclick="return confirm('Proceed to checkout this parking?');">
                                    Checkout
                                </a>
                            <?php else: ?>
                                <span style="color:gray;">N/A</span>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endwhile; ?>
            </table>

        </div>

    </div>

    <script>
        function filterTable(status) {
            const rows = document.querySelectorAll("#bookingTable tr[data-status]");
            const buttons = document.querySelectorAll(".filter-btn");

            buttons.forEach(btn => btn.classList.remove("active"));
            event.target.classList.add("active");

            rows.forEach(row => {
                if (status === "all" || row.dataset.status === status) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>

</body>

</html>