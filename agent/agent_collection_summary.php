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

// Daily collection summary
$daily_query = $conn->prepare("SELECT DATE(created_at) as collection_date, SUM(weight) as total_weight, COUNT(*) as total_requests
                               FROM request_collection
                               WHERE area_id=? AND status='Collected'
                               GROUP BY DATE(created_at)
                               ORDER BY collection_date DESC
                               LIMIT 30");
$daily_query->bind_param("i", $area_id);
$daily_query->execute();
$daily_result = $daily_query->get_result();

// Monthly collection summary
$monthly_query = $conn->prepare("SELECT YEAR(created_at) as year, MONTH(created_at) as month, SUM(weight) as total_weight, COUNT(*) as total_requests
                                 FROM request_collection
                                 WHERE area_id=? AND status='Collected'
                                 GROUP BY YEAR(created_at), MONTH(created_at)
                                 ORDER BY year DESC, month DESC
                                 LIMIT 12");
$monthly_query->bind_param("i", $area_id);
$monthly_query->execute();
$monthly_result = $monthly_query->get_result();

// Total collected this month
$current_month = date('Y-m');
$month_total_query = $conn->prepare("SELECT SUM(weight) as total_weight, COUNT(*) as total_requests
                                     FROM request_collection
                                     WHERE area_id=? AND status='Collected' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$month_total_query->bind_param("is", $area_id, $current_month);
$month_total_query->execute();
$month_total = $month_total_query->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Collection Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f0f2f5;
        }
        .content {
            margin-top: 80px;
            padding: 20px;
        }
        .summary-container {
            max-width: 1200px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #388e3c;
            margin-bottom: 20px;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            flex: 1;
            text-align: center;
        }
        .stat-box h3 {
            margin: 0;
            color: #2e7d32;
        }
        .stat-box p {
            margin: 5px 0 0 0;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }
        th {
            background: #66bb6a;
            color: #fff;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .section {
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
<?php include('agent_navbar.php'); ?>

<div class="content">
    <div class="summary-container">
        <h2>Collection Summary for <?= htmlspecialchars($agent_name) ?></h2>

        <div class="stats">
            <div class="stat-box">
                <h3><?php echo number_format($month_total['total_weight'] ?? 0, 2); ?> kg</h3>
                <p>Total Weight This Month</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $month_total['total_requests'] ?? 0; ?></h3>
                <p>Total Requests This Month</p>
            </div>
        </div>

        <div class="section">
            <h3>Daily Collection Summary (Last 30 Days)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total Weight (kg)</th>
                        <th>Total Requests</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($daily_result->num_rows > 0): ?>
                        <?php while($row = $daily_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['collection_date']); ?></td>
                                <td><?php echo number_format($row['total_weight'], 2); ?></td>
                                <td><?php echo $row['total_requests']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No collection data found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h3>Monthly Collection Summary</h3>
            <table>
                <thead>
                    <tr>
                        <th>Month/Year</th>
                        <th>Total Weight (kg)</th>
                        <th>Total Requests</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($monthly_result->num_rows > 0): ?>
                        <?php while($row = $monthly_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['month'] . '/' . $row['year']); ?></td>
                                <td><?php echo number_format($row['total_weight'], 2); ?></td>
                                <td><?php echo $row['total_requests']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No collection data found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$area_query->close();
$daily_query->close();
$monthly_query->close();
$month_total_query->close();
$conn->close();
?>
</body>
</html>
