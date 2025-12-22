<?php
// Make sure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
}
?>
<div class="sidebar">

    <!-- PROFILE SECTION -->
    <div class="profile-box" style="text-align:center; margin-bottom:20px;">

        <h3 style="color:#0A3D62; margin-top:10px;">
            Welcome! <?= $_SESSION['UserName']; ?>
        </h3>

        <a href="../Module1/student_edit_profile.php?id=<?= $_SESSION['UserID']; ?>" 
           style="font-size:13px; color:#1B98E0; font-weight:600; text-decoration:none;">
            Manage Profile ⚙️
        </a>
    </div>

    <a href="../Module1/student_dashboard.php">🏠 Dashboard</a>
    <a href="../Module1/student_vehicle_registration.php">🚗 Register Vehicle</a>
    <a href="../Module1/student_view_vehicle.php">📄 My Vehicles</a>
    <!-- module 3 -->
    <a href="../Module3/booking_form.php">🅿️ Parking Booking</a>
    <a href="../Module3/booking_list.php">📄 Views Booking</a>
    
    <!-- module 4 -->
     <a href="../Module4/student_demerit_points.php">⚠️ My Demerit Points</a>
    <a href="../Module1/logout.php" style="color:#B30000;">🚪 Logout</a>
</div>

