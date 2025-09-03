<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

$agent_id = $_SESSION['agent_id'];

// Fetch agent's area
$area_query = $conn->prepare("SELECT area_id FROM agent WHERE id = ?");
$area_query->bind_param("i", $agent_id);
$area_query->execute();
$area_result = $area_query->get_result();
$area_id = $area_result->fetch_assoc()['area_id'];
$area_query->close();

// Fetch schedules for agent's area
$stmt = $conn->prepare("SELECT collection_date, collection_time, waste_type, remarks, status FROM schedule WHERE area_id = ? ORDER BY collection_date ASC");
$stmt->bind_param("i", $area_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include('agent_navbar.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Agent Pickups</title>
    <link rel="stylesheet" href="../assets/css/agent_styles.css">
    <style>
        .page_content {
            padding-top: 80px;
        }
        .schedule_container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .center_heading {
            text-align: center;
            color: #388e3c;
            margin-bottom: 20px;
        }
        .schedule_table {
            width: 100%;
            border-collapse: collapse;
        }
        .schedule_table th, .schedule_table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        .schedule_table th {
            background-color: #388e3c;
            color: white;
        }
        .schedule_table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .schedule_table tr:hover {
            background-color: #d9f0d9;
        }
        .dashboard_btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #66bb6a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .dashboard_btn:hover {
            background-color: #388e3c;
        }
    </style>
</head>
<body>
<div class="page_content">
    <div class="schedule_container">
        <h2 class="center_heading">Pickup Schedules for Your Area</h2>
        <table class="schedule_table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Waste Type</th>
                    <th>Remarks</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['collection_date']); ?></td>
                            <td><?= htmlspecialchars($row['collection_time']); ?></td>
                            <td><?= htmlspecialchars($row['waste_type']); ?></td>
                            <td><?= htmlspecialchars($row['remarks']); ?></td>
                            <td><?= htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No pickup schedules available for your area currently.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <!-- <a href="agent_dashboard.php" class="dashboard_btn">⬅ Back to Dashboard</a> -->
    </div>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
