<?php
session_start();
include("../db_connect/db_connect.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$user_name = $_SESSION['admin_name'];

// Fetch summary stats
$total_requests_query = $conn->prepare("SELECT COUNT(*) as total FROM request_collection");
$total_requests_query->execute();
$total_requests = $total_requests_query->get_result()->fetch_assoc()['total'];

$pending_requests_query = $conn->prepare("SELECT COUNT(*) as pending FROM request_collection WHERE status='Pending'");
$pending_requests_query->execute();
$pending_requests = $pending_requests_query->get_result()->fetch_assoc()['pending'];

$collected_requests_query = $conn->prepare("SELECT COUNT(*) as collected FROM request_collection WHERE status='Collected'");
$collected_requests_query->execute();
$collected_requests = $collected_requests_query->get_result()->fetch_assoc()['collected'];

// Fetch last 5 requests
$recent_requests_query = $conn->prepare("SELECT request_id, name, waste_type, weight, status, created_at FROM request_collection ORDER BY created_at DESC LIMIT 5");
$recent_requests_query->execute();
$recent_requests = $recent_requests_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Admin Dashboard</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        /* ================= GENERAL STYLES ================= */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #e8f5e9, #f0fff0);
            color: #333;
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }

        /* ================= WELCOME ================= */
        .welcome {
            text-align: center;
            margin-bottom: 30px;
        }
        .welcome h1 {
            color: #388e3c;
            margin-bottom: 5px;
        }
        .welcome p {
            color: #555;
            font-size: 16px;
        }

        /* ================= CARDS ================= */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        .card h3 {
            margin: 0;
            color: #388e3c;
            font-size: 16px;
        }

        /* ================= STATS ================= */
        .stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 15px;
            width: 200px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-box:hover {
            transform: translateY(-5px);
        }
        .stat-box h2 {
            color: #388e3c;
            margin-bottom: 5px;
            font-size: 28px;
        }
        .stat-box p {
            color: #555;
            margin: 0;
            font-weight: 500;
        }

        /* ================= REQUESTS TABLE ================= */
        .requests-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .requests-table th {
            background-color: #388e3c;
            color: #fff;
            padding: 12px;
        }
        .requests-table td {
            padding: 12px;
            text-align: center;
        }
        .requests-table tr:nth-child(even) {
            background-color: #f0fff0;
        }
        .requests-table tr:hover {
            background-color: #d9f0d9;
        }

        .status.pending {
            background-color: #f6a644;
            color: #fff;
            border-radius: 12px;
            padding: 5px 10px;
            display: inline-block;
        }
        .status.collected {
            background-color: #388e3c;
            color: #fff;
            border-radius: 12px;
            padding: 5px 10px;
            display: inline-block;
        }

        /* ================= ECO TIPS ================= */
        .tips {
            background-color: #ffffff;
            border-left: 5px solid #66bb6a;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .tips h3 {
            color: #388e3c;
            margin-bottom: 10px;
        }
        .tips ul {
            padding-left: 20px;
            list-style-type: disc;
            color: #555;
            font-size: 14px;
        }
        .tips ul li {
            margin-bottom: 8px;
        }

        /* ================= FOOTER ================= */
        footer {
            text-align: center;
            padding: 15px;
            color: #555;
            font-size: 14px;
        }

        /* ================= RESPONSIVE ================= */
        @media(max-width: 992px){
            .cards { grid-template-columns: repeat(2, 1fr); }
            .stats { flex-direction: column; align-items: center; }
        }
        @media(max-width: 600px){
            .cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Welcome Section -->
    <div class="welcome">
        <h1>Hello, <?= htmlspecialchars($user_name) ?>!</h1>
        <p>Manage and oversee waste collection requests efficiently.</p>
    </div>

    <!-- Quick Action Cards -->
    <div class="cards">
        <div class="card" onclick="location.href='admin_add_schedules.php'"><h3>Add Schedule</h3></div>
        <div class="card" onclick="location.href='admin_add_agent.php'"><h3>Add Agent</h3></div>
        <div class="card" onclick="location.href='admin_view_request.php'"><h3>View Requests</h3></div>
        <div class="card" onclick="location.href='admin_view_user_complaints.php'"><h3>View Complaints</h3></div>
        <div class="card" onclick="location.href='admin_user_list.php'"><h3>User List</h3></div>
        <div class="card" onclick="location.href='admin_agent_list.php'"><h3>Agent List</h3></div>
        <div class="card" onclick="location.href='admin_add_area.php'"><h3>Add Area</h3></div>
        <div class="card" onclick="location.href='admin_edit_area.php'"><h3>Edit Area</h3></div>
        <div class="card" onclick="location.href='admin_add_admin.php'"><h3>Add Admin</h3></div>
        <div class="card" onclick="location.href='admin_logout.php'"><h3>Logout</h3></div>
    </div>

    <!-- Stats Section -->
    <div class="stats">
        <div class="stat-box"><h2><?= $total_requests ?></h2><p>Total Requests</p></div>
        <div class="stat-box"><h2><?= $pending_requests ?></h2><p>Pending</p></div>
        <div class="stat-box"><h2><?= $collected_requests ?></h2><p>Collected</p></div>
    </div>

    <!-- Footer -->
    <footer>
        &copy; 2025 EcoCollect. All rights reserved.
    </footer>
</div>
</body>
</html>

<?php
$total_requests_query->close();
$pending_requests_query->close();
$collected_requests_query->close();
$recent_requests_query->close();
$conn->close();
?>
