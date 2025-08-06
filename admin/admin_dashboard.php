<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
$user_name = $_SESSION['admin_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | User Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_styles.css">
</head>
<body>
    <div class="dashboard_container">
        <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <a href="admin_add_schedules.php" class="dashboard_btn">Add Shedule</a>
        <a href="admin_add_agent.php" class="dashboard_btn">Add Agent</a>

        </div>
</body>
</html>
