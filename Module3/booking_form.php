<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config.php';

// Allow access ONLY for Student role
if (!isset($_SESSION['UserRole']) || $_SESSION['UserRole'] != 'Student') {
    header("Location: ../index.php");
    exit();
}

$userID = $_SESSION['UserID'];
$userRole = $_SESSION['UserRole'];

// ===============================
// Load All AVAILABLE Parking Spaces
// ===============================
// Correct table names: parking_space + parking_area
$sql = "
    SELECT ps.ParkingSpaceID, ps.SpaceCode, pa.AreaName
    FROM parking_space ps
    JOIN parking_area pa ON ps.ParkingAreaID = pa.ParkingAreaID
    WHERE ps.StatusID = 'ST01'
    ORDER BY pa.AreaName, ps.SpaceCode
";

$spaces = mysqli_query($conn, $sql);

// Error handling if query fails
if (!$spaces) {
    die("SQL Error: " . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Parking Booking</title>
    <link rel="stylesheet" href="../templates/student_style.css">
    <link rel="stylesheet" href="_Module3CSS.css">
</head>

<body>

    <?php include '../templates/student_sidebar.php'; ?>

    <div class="main-content">

        <div class="header">🅿️ Parking Booking</div>

        <div class="form-box">

            <form method="POST" action="save_booking.php">

                <input type="hidden" name="StudentID" value="<?= $userID ?>">

                <label>Booking Date</label>
                <input type="date" name="BookingDate" required>

                <label>Start Time</label>
                <input type="time" name="StartTime" required>

                <label>End Time</label>
                <input type="time" name="EndTime" required>

                <label>Select Parking Space</label>
                <select name="SpaceID" required>
                    <option value="">-- Choose Space --</option>

                    <?php while ($s = mysqli_fetch_assoc($spaces)) : ?>
                        <option value="<?= $s['ParkingSpaceID'] ?>">
                            <?= $s['AreaName'] ?> - <?= $s['SpaceCode'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Confirm Booking</button>

            </form>
        </div>
    </div>

</body>

</html>