<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's area
$query = $conn->prepare("SELECT area_id FROM user WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$query->bind_result($area_id);
$query->fetch();
$query->close();

// Fetch schedules
$stmt = $conn->prepare("SELECT collection_date, collection_time, waste_type, remarks, status FROM schedule WHERE area_id = ? ORDER BY collection_date ASC");
$stmt->bind_param("i", $area_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | View Schedule</title>
    <link rel="stylesheet" href="../assets/css/user_styles.css">
</head>
<body>
    <div class="schedule_container">
        <h2 class="center_heading">Area-wise Collection Schedule</h2>
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
                        <td colspan="5">No schedules available for your area currently.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
            <a href="view_all_schedules.php" class="dashboard_btn">View All Area Schedules</a>
            <a href="user_dashboard.php" class="dashboard_btn">⬅ Back to Dashboard</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
