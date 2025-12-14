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

        <h3 style="color:#5A4B00; margin-top:10px;">
            Welcome! <?= $_SESSION['UserName']; ?>
        </h3>

        <a href="../Module1/admin_edit_profile.php?id=<?= $_SESSION['UserID']; ?>" 
           style="font-size:13px; color:#FF7A00; font-weight:600; text-decoration:none;">
            Manage Profile ⚙️
        </a>
    </div>

    <a href="../Module1/admin_dashboard.php">🏠 Dashboard</a>
    <a href="../Module2/manage_parking_area.php">📁 Manage Areas</a>
    <a href="../Module2/manage_spaces.php">🅿️ Manage Spaces</a>
    <a href="../Module2/daily_availability.php">📊 Daily Availability</a>
    <a href="../Module3/admin_booking_dashboard.php">📉 Book Analysis Dashboard</a>
    <a href="../Module1/admin_user_registration.php">👤 Student Registration</a>
    <a href="../Module1/admin_user_list.php">📑 View Profiles</a>
    <a href="../Module1/logout.php" style="color:#B30000;">🚪 Logout</a>
</div>
