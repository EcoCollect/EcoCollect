<?php
session_start();
include('../db_connect/db_connect.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area_id = $_POST['area_id'];
    $collection_date = $_POST['collection_date'];
    $collection_time = $_POST['collection_time'];
    $waste_type = $_POST['waste_type'];
    $remarks = $_POST['remarks'] ?? '';
    $status = $_POST['status'] ?? 'Scheduled'; // use default if empty

    $stmt = $conn->prepare("INSERT INTO schedule (area_id, collection_date, collection_time, waste_type, remarks, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $area_id, $collection_date, $collection_time, $waste_type, $remarks, $status);

    if ($stmt->execute()) {
        $success = "✅ Schedule added successfully!";
    } else {
        $error = "❌ Error adding schedule: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch areas for dropdown
$areas_result = $conn->query("SELECT area_id, area_name FROM area ORDER BY area_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Schedule | EcoCollect Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_styles.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>
    <div class="form_container">
        <h2>Add New Collection Schedule</h2>

        <?php if (!empty($success)): ?>
            <p style="color: green;"><?= $success ?></p>
        <?php elseif (!empty($error)): ?>
            <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Area:</label>
            <select name="area_id" required>
                <option value="">-- Select Area --</option>
                <?php while ($row = $areas_result->fetch_assoc()): ?>
                    <option value="<?= $row['area_id']; ?>"><?= htmlspecialchars($row['area_name']); ?></option>
                <?php endwhile; ?>
            </select>

            <label>Collection Date:</label>
            <input type="date" name="collection_date" required>

            <label>Collection Time:</label>
            <input type="time" name="collection_time" required>

            <label>Waste Type:</label>
            <input type="text" name="waste_type" required placeholder="e.g., Plastic, Organic">

            <label>Remarks (optional):</label>
            <textarea name="remarks" rows="3" placeholder="Any extra info..."></textarea>

            <label>Status:</label>
            <select name="status">
                <option value="Scheduled">Scheduled</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>

            <button type="submit" class="green_btn">➕ Add Schedule</button>
            <a href="admin_dashboard.php" class="dashboard_btn">⬅ Back to Dashboard</a>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>
