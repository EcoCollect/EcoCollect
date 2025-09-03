<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$result = $conn->query("SELECT s.schedule_id, a.area_name, s.collection_date, s.collection_time, s.waste_type, s.status, s.remarks
                        FROM schedule s
                        JOIN area a ON s.area_id = a.area_id
                        ORDER BY s.collection_date DESC, s.collection_time DESC");

// Expire schedules where date has passed and status is Scheduled
$conn->query("UPDATE schedule SET status = 'Expired' WHERE collection_date < CURDATE() AND status = 'Scheduled'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | View Schedules</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f0f2f5;
        }
        .content {
            margin-top: 80px; /* space for navbar */
            padding: 20px;
        }
        .table-container {
            max-width: 1200px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #ccc; text-align: left; }
        th { background: #66bb6a; color: #fff; }
        tr:hover { background: #f1f1f1; }
        .status { font-weight: bold; }
        .Scheduled { color: blue; }
        .Completed { color: green; }
        .Cancelled { color: red; }
        .Expired { color: orange; }
        .add-btn {
            background: #388e3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .add-btn:hover { background: #2e7d32; }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="content">
    <div class="table-container">
        <h2>All Collection Schedules</h2>
        <a href="admin_add_schedules.php" class="add-btn">➕ Add New Schedule</a>

        <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Schedule ID</th>
                    <th>Area</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Waste Type</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['schedule_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['area_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['collection_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['collection_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['waste_type']); ?></td>
                    <td class="status <?php echo $row['status']; ?>"><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['remarks'] ?? 'N/A'); ?></td>
                    <td>
                        <button onclick="window.location.href='admin_edit_schedule.php?schedule_id=<?php echo $row['schedule_id']; ?>'" style="background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Edit</button>
                        <button onclick="if(confirm('Are you sure you want to delete this schedule?')) window.location.href='admin_delete_schedule.php?schedule_id=<?php echo $row['schedule_id']; ?>';" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-left: 5px;">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No schedules found.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
