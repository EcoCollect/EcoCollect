<?php
$current_page = basename($_SERVER['PHP_SELF']); // Get current file name
?>
<div class="navbar">
    <a href="user_dashboard.php" class="<?php echo ($current_page == 'user_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
    <a href="user_view_schedule.php" class="<?php echo ($current_page == 'user_view_schedule.php') ? 'active' : ''; ?>">View Schedule</a>
    <a href="user_request.php" class="<?php echo ($current_page == 'user_request.php') ? 'active' : ''; ?>">Request Collection</a>
    <a href="user_track_request.php" class="<?php echo ($current_page == 'user_track_request.php') ? 'active' : ''; ?>">Track Requests</a>
    <a href="user_complaint.php" class="<?php echo ($current_page == 'user_complaint.php') ? 'active' : ''; ?>">Complaints & Feedback</a>
    <a href="user_profile.php" class="<?php echo ($current_page == 'user_profile.php') ? 'active' : ''; ?>">Update Profile</a>
    <a href="user_logout.php">Logout</a>
</div>

<style>
/* Navbar Styling */
.navbar {
    background-color: #388e3c;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
}

.navbar a {
    color: white;
    text-decoration: none;
    padding: 10px 18px;
    margin: 0 5px;
    border-radius: 6px;
    font-weight: bold;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.navbar a:hover {
    background-color: #2e7d32;
    color: #f1f1f1;
}

.navbar a.active {
    background-color: #66bb6a;
    color: white;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
}

.content {
    margin-top: 80px; /* leave space for fixed navbar */
    padding: 20px;
}
</style>
