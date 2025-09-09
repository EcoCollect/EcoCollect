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

// Fetch all requests in area for detailed summary
$all_requests_query = $conn->prepare("SELECT name, waste_type, weight, status, created_at FROM request_collection WHERE area_id=? ORDER BY created_at DESC");
$all_requests_query->bind_param("i", $area_id);
$all_requests_query->execute();
$all_requests = $all_requests_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Agent Collection Summary</title>

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
            margin: 100px auto 30px auto;
            padding: 20px;
        }

        /* ================= HEADER ================= */
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #388e3c;
            margin-bottom: 5px;
        }
        .header p {
            color: #555;
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
            width: 250px;
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
            font-size: 32px;
        }
        .stat-box p {
            color: #555;
            margin: 0;
            font-weight: 500;
            font-size: 18px;
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

        /* ================= FOOTER ================= */
        footer {
            text-align: center;
            padding: 15px;
            color: #555;
            font-size: 14px;
        }

        /* ================= RESPONSIVE ================= */
        @media(max-width: 992px){
            .stats { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>
<?php include("agent_navbar.php"); ?>
<div class="container">
    <!-- Header Section -->
    <div class="header">
        <h1>Collection Summary</h1>
        <p>Overview of waste collection requests in your assigned area.</p>
    </div>

    <!-- Stats Section -->
    <div class="stats">
        <div class="stat-box">
            <h2><?= $total_requests ?></h2>
            <p>Total Requests</p>
        </div>
        <div class="stat-box">
            <h2><?= $pending_requests ?></h2>
            <p>Pending</p>
        </div>
        <div class="stat-box">
            <h2><?= $collected_requests ?></h2>
            <p>Collected</p>
        </div>
    </div>

    <!-- All Requests Table -->
    <table class="requests-table">
        <thead>
            <tr>
                <th>User Name</th>
                <th>Waste Type</th>
                <th>Weight (kg)</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if($all_requests->num_rows > 0): ?>
                <?php while($row = $all_requests->fetch_assoc()): ?>
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
                        <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No requests found in your area.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <footer>
        &copy; 2025 EcoCollect. All rights reserved.
    </footer>
</div>
</body>
</html>
