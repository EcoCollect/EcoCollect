<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}
$user_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | User Dashboard</title>
    <link rel="stylesheet" href="../assets/css/user_styles.css">
</head>
<body>
    <div class="dashboard_container">
        <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <a href="request_collection.php" class="dashboard_btn">Request Collection</a>
        <a href="collection_history.php" class="dashboard_btn">View Collection History</a>
        <a href="user_view_schedule.php" class="dashboard_btn">View Area-Wise Schedule</a>
        <a href="user_logout.php" class="dashboard_btn">Logout</a>
    </div>
</body>
</html>
