<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$schedule_id = $_GET['schedule_id'] ?? null;
if (!$schedule_id) {
    header("Location: admin_view_schedules.php");
    exit();
}

// Fetch schedule data
$stmt = $conn->prepare("SELECT s.*, a.area_name FROM schedule s JOIN area a ON s.area_id = a.area_id WHERE s.schedule_id = ?");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();
$stmt->close();

if (!$schedule) {
    header("Location: admin_view_schedules.php");
    exit();
}

// Check if schedule is expired
if ($schedule['status'] === 'Expired') {
    $error = "❌ This schedule has expired and cannot be edited.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area_id = $_POST['area_id'];
    $collection_date = $_POST['collection_date'];
    $collection_time = $_POST['collection_time'];
    $waste_type = $_POST['waste_type'];
    $remarks = $_POST['remarks'] ?? '';
    $status = $_POST['status'];

    $update_stmt = $conn->prepare("UPDATE schedule SET area_id=?, collection_date=?, collection_time=?, waste_type=?, remarks=?, status=? WHERE schedule_id=?");
    $update_stmt->bind_param("isssssi", $area_id, $collection_date, $collection_time, $waste_type, $remarks, $status, $schedule_id);

    if ($update_stmt->execute()) {
        $success = "✅ Schedule updated successfully!";
        // Refresh data
        $stmt = $conn->prepare("SELECT s.*, a.area_name FROM schedule s JOIN area a ON s.area_id = a.area_id WHERE s.schedule_id = ?");
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule = $result->fetch_assoc();
        $stmt->close();
    } else {
        $error = "❌ Error updating schedule: " . $update_stmt->error;
    }
    $update_stmt->close();
}

// Fetch areas for dropdown
$areas_result = $conn->query("SELECT area_id, area_name FROM area ORDER BY area_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Schedule | EcoCollect Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_styles.css">
    <style>
        .form_container {
            max-width: 550px;
            margin: 80px auto 30px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .form_container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #388e3c;
            font-size: 26px;
            font-weight: 600;
        }

        .form_container form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            align-items: start;
        }

        .form_container form > div {
            display: flex;
            flex-direction: column;
        }

        .form_container form > div.full-width {
            grid-column: 1 / -1;
        }

        .form_container label {
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form_container input,
        .form_container select,
        .form_container textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            background: #fafafa;
        }

        .form_container input:focus,
        .form_container select:focus,
        .form_container textarea:focus {
            outline: none;
            border-color: #388e3c;
            box-shadow: 0 0 5px rgba(56, 142, 60, 0.3);
            background: white;
        }

        .form_container textarea {
            resize: vertical;
            min-height: 70px;
            font-family: inherit;
        }

        .form_container input[type="date"],
        .form_container input[type="time"] {
            cursor: pointer;
        }

        .form_container .green_btn {
            background: linear-gradient(135deg, #388e3c, #66bb6a);
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            grid-column: 1 / -1;
            justify-self: center;
            min-width: 180px;
        }

        .form_container .green_btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(56, 142, 60, 0.3);
        }

        .form_container .dashboard_btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
            margin-top: 15px;
            grid-column: 1 / -1;
            justify-self: center;
            font-size: 14px;
        }

        .form_container .dashboard_btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
            grid-column: 1 / -1;
            font-size: 14px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .form_container {
                margin: 70px 15px 25px 15px;
                padding: 20px;
            }

            .form_container form {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .form_container h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
    <div class="form_container">
        <h2>Edit Collection Schedule</h2>

        <?php if (!empty($success)): ?>
            <div class="message success"><?= $success ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($schedule['status'] !== 'Expired'): ?>
        <form method="post">
            <div>
                <label>Area:</label>
                <select name="area_id" required>
                    <option value="">-- Select Area --</option>
                    <?php while ($row = $areas_result->fetch_assoc()): ?>
                        <option value="<?= $row['area_id']; ?>" <?= $row['area_id'] == $schedule['area_id'] ? 'selected' : ''; ?>><?= htmlspecialchars($row['area_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label>Collection Date:</label>
                <input type="date" name="collection_date" value="<?= htmlspecialchars($schedule['collection_date']); ?>" required>
            </div>

            <div>
                <label>Collection Time:</label>
                <input type="time" name="collection_time" value="<?= htmlspecialchars($schedule['collection_time']); ?>" required>
            </div>

            <div>
                <label>Waste Type:</label>
                <input type="text" name="waste_type" value="<?= htmlspecialchars($schedule['waste_type']); ?>" required placeholder="e.g., Plastic, Organic">
            </div>

            <div class="full-width">
                <label>Remarks (optional):</label>
                <textarea name="remarks" rows="3" placeholder="Any extra information..."><?= htmlspecialchars($schedule['remarks'] ?? ''); ?></textarea>
            </div>

            <div>
                <label>Status:</label>
                <select name="status">
                    <option value="Scheduled" <?= $schedule['status'] == 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="Completed" <?= $schedule['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Cancelled" <?= $schedule['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="Expired" <?= $schedule['status'] == 'Expired' ? 'selected' : ''; ?>>Expired</option>
                </select>
            </div>

            <div class="full-width" style="display: flex; gap: 20px; justify-content: center;">
                <button type="submit" class="green_btn">✏️ Update Schedule</button>
                <a href="admin_view_schedules.php" class="dashboard_btn">⬅ Back to Schedules</a>
            </div>
        </form>
        <?php else: ?>
            <p style="text-align: center; color: #721c24; font-weight: bold;">This schedule has expired and cannot be edited.</p>
            <div class="full-width" style="display: flex; gap: 20px; justify-content: center;">
                <a href="admin_view_schedules.php" class="dashboard_btn">⬅ Back to Schedules</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
