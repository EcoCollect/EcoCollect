<?php
session_start();
include("../db_connect/db_connect.php");

if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

$agent_name = $_SESSION['agent_name'];
$agent_id = $_SESSION['agent_id'];

// Fetch agent's area
$area_query = $conn->prepare("SELECT area_id FROM agent WHERE id=?");
$area_query->bind_param("i", $agent_id);
$area_query->execute();
$area_result = $area_query->get_result();
$area_id = $area_result->fetch_assoc()['area_id'];

// Fetch summary stats for agent's area
$total_requests_query = $conn->prepare("SELECT COUNT(*) as total FROM request_collection WHERE area_id=?");
$total_requests_query->bind_param("i", $area_id);
$total_requests_query->execute();
$total_requests = $total_requests_query->get_result()->fetch_assoc()['total'];

$pending_requests_query = $conn->prepare("SELECT COUNT(*) as pending FROM request_collection WHERE area_id=? AND status='Pending'");
$pending_requests_query->bind_param("i", $area_id);
$pending_requests_query->execute();
$pending_requests = $pending_requests_query->get_result()->fetch_assoc()['pending'];

$collected_requests_query = $conn->prepare("SELECT COUNT(*) as collected FROM request_collection WHERE area_id=? AND status='Collected'");
$collected_requests_query->bind_param("i", $area_id);
$collected_requests_query->execute();
$collected_requests = $collected_requests_query->get_result()->fetch_assoc()['collected'];

// Fetch last 5 requests in area
$recent_requests_query = $conn->prepare("SELECT name, waste_type, weight, status FROM request_collection WHERE area_id=? ORDER BY created_at DESC LIMIT 5");
$recent_requests_query->bind_param("i", $area_id);
$recent_requests_query->execute();
$recent_requests = $recent_requests_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Agent Dashboard</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="container">

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
        <h1>Hello, <?= htmlspecialchars($agent_name) ?>!</h1>
        <p>Manage waste collection tasks in your assigned area efficiently.</p>
    </div>

    <!-- Quick Action Cards -->
    <div class="cards">
        <div class="card" onclick="location.href='agent_pickups.php'"><h3>Pickup Schedules</h3></div>
        <div class="card" onclick="location.href='agent_view_schedule.php'"><h3>View Schedules</h3></div>
        <div class="card" onclick="location.href='agent_collection_summary.php'"><h3>Collection Summary</h3></div>
        <div class="card" onclick="location.href='agent_complaint.php'"><h3>Complaints/Feedback</h3></div>
        <div class="card" onclick="location.href='agent_profile.php'"><h3>My Profile</h3></div>
        <div class="card" onclick="location.href='agent_logout.php'"><h3>Logout</h3></div>
    </div>

    <!-- Stats Section -->
    <div id="stats" class="stats">
        <div class="stat-box"><h2><?= $total_requests ?></h2><p>Total Requests</p></div>
        <div class="stat-box"><h2><?= $pending_requests ?></h2><p>Pending</p></div>
        <div class="stat-box"><h2><?= $collected_requests ?></h2><p>Collected</p></div>
    </div>

    <!-- Recent Requests Table -->
    <table class="requests-table">
        <thead>
            <tr>
                <th>User Name</th>
                <th>Waste Type</th>
                <th>Weight (kg)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if($recent_requests->num_rows > 0): ?>
                <?php while($row = $recent_requests->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['waste_type']) ?></td>
                        <td><?= $row['weight'] ?></td>
                        <td>
                            <?php if($row['status']=='Pending'): ?>
                                <span class="status pending">Pending</span>
                            <?php elseif($row['status']=='Collected'): ?>
                                <span class="status collected">Collected</span>
                            <?php else: ?>
                                <?= htmlspecialchars($row['status']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No recent requests found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Eco Tips Section -->
    <div class="tips">
        <h3>Eco Tips for Agents</h3>
        <ul>
            <li>Ensure safe handling of waste materials to protect yourself and the environment.</li>
            <li>Sort waste properly at collection points for efficient recycling.</li>
            <li>Educate residents on waste segregation to improve collection quality.</li>
            <li>Use eco-friendly vehicles and maintain them for reduced emissions.</li>
            <li>Report any hazardous waste found during collections immediately.</li>
            <li>Keep records of collections for accurate reporting and planning.</li>
            <li>Promote community awareness on sustainable waste management.</li>
            <li>Stay updated on local environmental regulations and best practices.</li>
        </ul>
    </div>

    <!-- Footer -->
    <footer>
        &copy; 2025 EcoCollect. All rights reserved.
    </footer>
</div>
</body>
</html>

<?php
$area_query->close();
$total_requests_query->close();
$pending_requests_query->close();
$collected_requests_query->close();
$recent_requests_query->close();
$conn->close();
?>
