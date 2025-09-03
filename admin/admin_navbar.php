<?php
$current_page = basename($_SERVER['PHP_SELF']); // Get current file name
?>
<div class="navbar">
    <a href="admin_dashboard.php" class="<?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>

    <div class="dropdown">
        <button class="dropbtn">User Management</button>
        <div class="dropdown-content">
            <a href="admin_user_list.php" class="<?php echo ($current_page == 'admin_user_list.php') ? 'active' : ''; ?>">Users</a>
            <a href="admin_view_user_complaints.php" class="<?php echo ($current_page == 'admin_view_user_complaints.php') ? 'active' : ''; ?>">User Complaints/Feedback</a>
        </div>
    </div>

    <div class="dropdown">
        <button class="dropbtn">Agent Management</button>
        <div class="dropdown-content">
            <a href="admin_agent_list.php" class="<?php echo ($current_page == 'admin_agent_list.php') ? 'active' : ''; ?>">Agents</a>
            <a href="admin_view_agent_complaints.php" class="<?php echo ($current_page == 'admin_view_agent_complaints.php') ? 'active' : ''; ?>">Agent Complaints/Feedback</a>
            <a href="admin_add_agent.php" class="<?php echo ($current_page == 'admin_add_agent.php') ? 'active' : ''; ?>">Add Agent</a>
        </div>
    </div>

    <div class="dropdown">
        <button class="dropbtn">Area Management</button>
        <div class="dropdown-content">
            <a href="admin_add_area.php" class="<?php echo ($current_page == 'admin_add_area.php') ? 'active' : ''; ?>">Add Area</a>
            <a href="admin_view_area.php" class="<?php echo ($current_page == 'admin_view_area.php') ? 'active' : ''; ?>">View Areas</a>
        </div>
    </div>

    <div class="dropdown">
        <button class="dropbtn" style="background-color: #388e3c; color: white;">Requests</button>
        <div class="dropdown-content">
            <a href="admin_view_request.php" class="<?php echo ($current_page == 'admin_view_request.php') ? 'active' : ''; ?>">View Requests</a>
            <a href="admin_view_waste_details.php" class="<?php echo ($current_page == 'admin_view_waste_details.php') ? 'active' : ''; ?>" style="background-color: #66bb6a; color: black;">Waste Details</a>
        </div>
    </div>

    <div class="dropdown">
        <button class="dropbtn">Schedules</button>
        <div class="dropdown-content">
            <a href="admin_view_schedules.php" class="<?php echo ($current_page == 'admin_view_schedules.php') ? 'active' : ''; ?>">View Schedules</a>
            <a href="admin_add_schedules.php" class="<?php echo ($current_page == 'admin_add_schedules.php') ? 'active' : ''; ?>">Add Schedule</a>
        </div>
    </div>

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

.navbar a, .dropbtn {
    color: white;
    text-decoration: none;
    padding: 10px 18px;
    margin: 0 5px;
    border-radius: 6px;
    font-weight: bold;
    transition: background-color 0.3s ease, color 0.3s ease;
    border: none;
    background: none;
    cursor: pointer;
}

.navbar a:hover, .dropbtn:hover {
    background-color: #2e7d32;
    color: #f1f1f1;
}

.navbar a.active {
    background-color: #66bb6a;
    color: white;
}

/* Dropdown container */
.dropdown {
    position: relative;
    display: inline-block;
}

/* Dropdown content (hidden by default) */
.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    border-radius: 6px;
    top: 100%;
    left: 0;
}

/* Links inside the dropdown */
.dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    margin: 0;
    border-radius: 0;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.dropdown-content a.active {
    background-color: #66bb6a;
    color: black;
}

/* Show the dropdown menu on hover */
.dropdown:hover .dropdown-content {
    display: block;
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
