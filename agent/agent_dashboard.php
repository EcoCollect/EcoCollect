<?php
session_start();
if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}
$agent_name = $_SESSION['agent_name']; // Store agent's name in session during login
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="../assets/css/agent_styles.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Welcome, <?php echo $agent_name; ?></h2>
            <ul>
                <li><a href="#">Dashboard</a></li>
                <li><a href="#">My Area</a></li>
                <li><a href="#">Assigned Tasks</a></li>
                <li><a href="#">Reports</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
            <a class="logout-btn" href="agent_logout.php">Logout</a>
        </div>

        <div class="main-content">
            <h1>Dashboard</h1>
            <p>Here you can manage your tasks, view area details and report your activities.</p>

            <div class="card">
                <h2>Today's Summary</h2>
                <p>Tasks Completed: 3</p>
                <p>Pending Pickups: 2</p>
            </div>

            <div class="card">
                <h2>Quick Actions</h2>
                <div class="actions">
                    <button onclick="location.href='pickup_list.php'">View Pickups</button>
                    <button onclick="location.href='submit_report.php'">Submit Report</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
