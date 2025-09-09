<?php
session_start();
include('../db_connect/db_connect.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Fetch all areas for the dropdown
$areas = [];
$area_stmt = $conn->prepare("SELECT area_id, area_name FROM area ORDER BY area_name ASC");
$area_stmt->execute();
$area_result = $area_stmt->get_result();
while ($row = $area_result->fetch_assoc()) {
    $areas[] = $row;
}
$area_stmt->close();

// Initialize selected area schedule data
$schedules = [];
$selected_area_id = null;
$selected_area_name = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['area_id'])) {
    $selected_area_id = intval($_POST['area_id']);

    // Get area name for display
    foreach ($areas as $a) {
        if ($a['area_id'] == $selected_area_id) {
            $selected_area_name = $a['area_name'];
            break;
        }
    }

    // Fetch schedules for selected area, excluding expired
    $stmt = $conn->prepare("SELECT collection_date, collection_time, waste_type, remarks, status FROM schedule WHERE area_id = ? AND status != 'Expired' ORDER BY collection_date ASC");
    $stmt->bind_param("i", $selected_area_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
    $stmt->close();
}
?>

<?php include 'user_navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | View All Area Schedules</title>
    <link rel="stylesheet" href="../assets/css/user_styles.css">
</head>
<body>
    <div class="page_content" style="padding-top: 80px;">
        <div class="schedule_container">
            <h2>View Area-wise Collection Schedule</h2>

            <form method="POST">
                <select name="area_id" class="input" required>
                    <option value="">-- Select Area --</option>
                    <?php foreach ($areas as $area): ?>
                        <option value="<?= htmlspecialchars($area['area_id']) ?>"
                            <?= ($area['area_id'] == $selected_area_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($area['area_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="green_btn">View Schedule</button>
            </form>

            <?php if ($selected_area_id !== null): ?>
                <h3>Schedule for <?= htmlspecialchars($selected_area_name) ?></h3>
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
                        <?php if (count($schedules) > 0): ?>
                            <?php foreach ($schedules as $schedule): ?>
                                <tr>
                                    <td><?= htmlspecialchars($schedule['collection_date']); ?></td>
                                    <td><?= htmlspecialchars($schedule['collection_time']); ?></td>
                                    <td><?= htmlspecialchars($schedule['waste_type']); ?></td>
                                    <td><?= htmlspecialchars($schedule['remarks']); ?></td>
                                    <td><?= htmlspecialchars($schedule['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No schedules available for this area currently.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <a href="user_dashboard.php" class="dashboard_btn">⬅ Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
