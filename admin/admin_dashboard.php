<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Get summary statistics
$user_count = $conn->query("SELECT COUNT(*) as count FROM user")->fetch_assoc()['count'];
$agent_count = $conn->query("SELECT COUNT(*) as count FROM agent")->fetch_assoc()['count'];
$request_count = $conn->query("SELECT COUNT(*) as count FROM request_collection")->fetch_assoc()['count'];
$schedule_count = $conn->query("SELECT COUNT(*) as count FROM schedule")->fetch_assoc()['count'];

// Get recent activities
$recent_requests = $conn->query("SELECT name, waste_type, status, created_at FROM request_collection ORDER BY created_at DESC LIMIT 5");
$recent_complaints = $conn->query("SELECT c.complaint_text, u.user_name, c.created_at FROM complaints c JOIN user u ON c.user_id = u.user_id ORDER BY c.created_at DESC LIMIT 3");
$recent_agent_complaints = $conn->query("SELECT ac.complaint_text, a.name, ac.created_at FROM agent_complaints ac JOIN agent a ON ac.user_id = a.id ORDER BY ac.created_at DESC LIMIT 3");

$user_name = $_SESSION['admin_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f0f2f5;
        }
        .content {
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-card h3 {
            margin: 0 0 5px 0;
            color: #388e3c;
            font-size: 1.5em;
        }
        .stat-card p {
            margin: 0;
            color: #666;
            font-weight: bold;
            font-size: 0.8em;
        }
        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section h2 {
            margin-top: 0;
            color: #388e3c;
            border-bottom: 2px solid #66bb6a;
            padding-bottom: 10px;
        }
        .recent-list {
            list-style: none;
            padding: 0;
        }
        .recent-list li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .recent-list li:last-child {
            border-bottom: none;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-collected { background: #d4edda; color: #155724; }
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .link-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        .link-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            color: #388e3c;
        }
        .link-card h4 {
            margin: 0 0 5px 0;
            font-size: 1.1em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
<div class="content">
    <div class="container">
        <h1 style="text-align: center; color: #388e3c; margin-bottom: 30px;">Admin Overview Dashboard</h1>

        <!-- Quick Links -->
        <div class="quick-links">
            <a href="admin_user_list.php" class="link-card">
                <h4>👥 Users</h4>
                <p>View all registered users</p>
            </a>
            <a href="admin_agent_list.php" class="link-card">
                <h4>🚛 Agents</h4>
                <p>Manage waste collection agents</p>
            </a>
            <a href="admin_view_area.php" class="link-card">
                <h4>🏘️ Areas</h4>
                <p>View and manage areas</p>
            </a>
            <a href="admin_view_request.php" class="link-card">
                <h4>📋 Requests</h4>
                <p>View collection requests</p>
            </a>
            <a href="admin_view_schedules.php" class="link-card">
                <h4>📅 Schedules</h4>
                <p>Manage collection schedules</p>
            </a>
            <a href="admin_view_user_complaints.php" class="link-card">
                <h4>💬 User Complaints/Feedback</h4>
                <p>View user feedback</p>
            </a>
            <a href="admin_view_agent_complaints.php" class="link-card">
                <h4>🗣️ Agent Complaints/Feedback</h4>
                <p>View agent feedback</p>
            </a>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $user_count; ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $agent_count; ?></h3>
                <p>Total Agents</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $request_count; ?></h3>
                <p>Collection Requests</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $schedule_count; ?></h3>
                <p>Active Schedules</p>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="section">
            <h2>Recent Collection Requests</h2>
            <ul class="recent-list">
                <?php while($row = $recent_requests->fetch_assoc()): ?>
                <li>
                    <strong><?php echo htmlspecialchars($row['name']); ?></strong> - <?php echo htmlspecialchars($row['waste_type']); ?>
                    <span class="status-badge status-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span>
                    <small style="color: #666;">(<?php echo date('M d, Y', strtotime($row['created_at'])); ?>)</small>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="section">
                <h2>Recent User Complaints/Feedback</h2>
                <ul class="recent-list">
                    <?php while($row = $recent_complaints->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($row['user_name']); ?>:</strong>
                        <?php echo htmlspecialchars(substr($row['complaint_text'], 0, 100)); ?>...
                        <small style="color: #666;">(<?php echo date('M d, Y', strtotime($row['created_at'])); ?>)</small>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="section">
                <h2>Recent Agent Complaints/Feedback</h2>
                <ul class="recent-list">
                    <?php while($row = $recent_agent_complaints->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($row['name']); ?>:</strong>
                        <?php echo htmlspecialchars(substr($row['complaint_text'], 0, 100)); ?>...
                        <small style="color: #666;">(<?php echo date('M d, Y', strtotime($row['created_at'])); ?>)</small>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html>
