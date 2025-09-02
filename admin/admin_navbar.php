 <?php
$current_page = basename($_SERVER['PHP_SELF']); // Get current file name
?>
<div class="navbar">
    <a href="admin_dashboard.php" class="<?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
    <a href="admin_add_schedules.php" class="<?php echo ($current_page == 'admin_add_schedules.php') ? 'active' : ''; ?>">Add Schedule</a>
    <a href="admin_add_agent.php" class="<?php echo ($current_page == 'admin_add_agent.php') ? 'active' : ''; ?>">Add Agent</a>
    <a href="admin_view_request.php" class="<?php echo ($current_page == 'admin_view_request.php') ? 'active' : ''; ?>">View Requests</a>
    <a href="admin_view_user_complaints.php" class="<?php echo ($current_page == 'admin_view_user_complaints.php') ? 'active' : ''; ?>">View Complaints</a>
    <a href="admin_user_list.php" class="<?php echo ($current_page == 'admin_user_list.php') ? 'active' : ''; ?>">User List</a>
    <a href="admin_agent_list.php" class="<?php echo ($current_page == 'admin_agent_list.php') ? 'active' : ''; ?>">Agent List</a>
    <a href="admin_add_area.php" class="<?php echo ($current_page == 'admin_add_area.php') ? 'active' : ''; ?>">Add Area</a>
    <a href="admin_edit_area.php" class="<?php echo ($current_page == 'admin_edit_area.php') ? 'active' : ''; ?>">Edit Area</a>
    <a href="admin_add_admin.php" class="<?php echo ($current_page == 'admin_add_admin.php') ? 'active' : ''; ?>">Add Admin</a>
    <a href="admin_logout.php">Logout</a>
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
