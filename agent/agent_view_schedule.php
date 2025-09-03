<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

$agent_id = $_SESSION['agent_id'];
$message = "";
$message_type = "";

// Check for message from update_status.php
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $message_type = $_GET['type'] ?? 'info';
}

// Fetch agent's area
$area_query = $conn->prepare("SELECT area_id FROM agent WHERE id = ?");
$area_query->bind_param("i", $agent_id);
$area_query->execute();
$area_result = $area_query->get_result();
$area_id = $area_result->fetch_assoc()['area_id'];
$area_query->close();

try {
    $stmt = $conn->prepare("
        SELECT r.request_id, r.name, r.waste_type, r.weight, r.status, r.created_at,
               s.collection_date, s.collection_time, r.remarks
        FROM request_collection r
        LEFT JOIN schedule s ON r.schedule_id = s.schedule_id
        WHERE r.area_id = ?
        ORDER BY r.created_at DESC
    ");

    if ($stmt === false) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("i", $area_id);
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Schedules - EcoCollect</title>
    <link rel="stylesheet" href="../assets/css/agent_styles.css">
    <style>
        .schedule-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .schedule-table th,
        .schedule-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .schedule-table th {
            background-color: #4CAF50;
            color: white;
        }
        .schedule-table tr:hover {
            background-color: #f5f5f5;
        }
        .status-pending {
            color: #ff9800;
            font-weight: bold;
        }
        .status-collected {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-cancelled {
            color: #f44336;
            font-weight: bold;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            margin-right: 5px;
        }
        .complete-btn {
            background-color: #4CAF50;
        }
        .cancel-btn {
            background-color: #f44336;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #4caf50;
        }
        .info-message {
            background-color: #e3f2fd;
            color: #1565c0;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        .no-schedules {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>
<?php include('agent_navbar.php'); ?>
<div class="schedule-container">
    <h1>My Collection Schedules</h1>

    <?php if ($message): ?>
        <div class="<?php echo $message_type; ?>-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (isset($result) && $result->num_rows > 0): ?>
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Collection Date</th>
                    <th>Collection Time</th>
                    <th>Name</th>
                    <th>Waste Type</th>
                    <th>Weight (kg)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['collection_date'] ?? 'Not Scheduled'); ?></td>
                        <td><?php echo htmlspecialchars($row['collection_time'] ?? 'Not Scheduled'); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['waste_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['weight']); ?></td>
                        <td>
                            <span class="status-<?php echo strtolower($row['status']); ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['status'] == 'Pending'): ?>
                                <a href="update_status.php?id=<?php echo $row['request_id']; ?>"
                                   class="action-btn complete-btn"
                                   onclick="return confirm('Mark this request as Collected?')">Mark Collected</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-schedules">
            <p>No collection schedules found.</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
<?php
if (isset($result) && $result instanceof mysqli_result) {
    $result->close();
}
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
$conn->close();
?>
