ing ei try<?php
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
        .form-container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            color: #388e3c;
            margin-bottom: 25px;
            text-align: center;
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-container input, .form-container select, .form-container textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-container input:focus, .form-container select:focus, .form-container textarea:focus {
            outline: none;
            border-color: #388e3c;
        }
        .form-container textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        .button-group button, .button-group a {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .green_btn {
            background-color: #388e3c;
            color: white;
        }
        .green_btn:hover {
            background-color: #2e7d32;
        }
        .dashboard_btn {
            background-color: #6c757d;
            color: white;
        }
        .dashboard_btn:hover {
            background-color: #5a6268;
        }
        .message {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="content">
    <div class="form-container">
        <h2>Add New Collection Schedule</h2>

        <?php if (!empty($success)): ?>
            <div class="message"><?= $success ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Area:</label>
            <select name="area_id" required>
                <option value="">-- Select Area --</option>
                <?php while ($row = $areas_result->fetch_assoc()): ?>
                    <option value="<?= $row['area_id']; ?>"><?= htmlspecialchars($row['area_name']); ?></option>
                <?php endwhile; ?>
            </select>

            <div class="form-row">
                <div class="form-group">
                    <label>Collection Date:</label>
                    <input type="date" name="collection_date" required>
                </div>
                <div class="form-group">
                    <label>Collection Time:</label>
                    <input type="time" name="collection_time" required>
                </div>
            </div>

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

            <div class="button-group">
                <button type="submit" class="green_btn">➕ Add Schedule</button>
                <a href="admin_dashboard.php" class="dashboard_btn">⬅ Back to Dashboard</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>

<?php $conn->close(); ?>
