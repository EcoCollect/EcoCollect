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
$current_date = date('Y-m-d');
if ($schedule['collection_date'] < $current_date && $schedule['status'] !== 'Expired') {
    $schedule['status'] = 'Expired';
    // Update status to Expired
    $update_stmt = $conn->prepare("UPDATE schedule SET status='Expired' WHERE schedule_id=?");
    $update_stmt->bind_param("i", $schedule_id);
    $update_stmt->execute();
    $update_stmt->close();
}

if ($schedule['status'] === 'Expired') {
    $error = "❌ This schedule has expired and cannot be edited.";
}

// Parse collection_time to 12-hour format
$time_parts = explode(':', $schedule['collection_time']);
$hour_24 = (int)$time_parts[0];
$minute = (int)$time_parts[1];
$ampm = $hour_24 >= 12 ? 'PM' : 'AM';
$hour_12 = $hour_24 % 12;
if ($hour_12 === 0) $hour_12 = 12;

// Parse waste_types
$selected_waste_types = explode(',', $schedule['waste_type']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area_id = (int)$_POST['area_id'];
    $collection_date = $_POST['collection_date'];
    $hour = (int)$_POST['hour'];
    $minute = (int)$_POST['minute'];
    $ampm = $_POST['ampm'];

    // Convert to 24-hour format
    if ($ampm === 'PM' && $hour !== 12) {
        $hour += 12;
    } elseif ($ampm === 'AM' && $hour === 12) {
        $hour = 0;
    }
    $collection_time = sprintf("%02d:%02d:00", $hour, $minute);

    $waste_types = isset($_POST['waste_type']) ? $_POST['waste_type'] : [];
    $waste_type = implode(',', $waste_types);
    $remarks = $_POST['remarks'] ?? '';
    $status = 'Scheduled'; // automatically set

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
        // Re-parse after update
        $time_parts = explode(':', $schedule['collection_time']);
        $hour_24 = (int)$time_parts[0];
        $minute = (int)$time_parts[1];
        $ampm = $hour_24 >= 12 ? 'PM' : 'AM';
        $hour_12 = $hour_24 % 12;
        if ($hour_12 === 0) $hour_12 = 12;
        $selected_waste_types = explode(',', $schedule['waste_type']);
    } else {
        $error = "❌ Error updating schedule: " . $update_stmt->error;
    }
    $update_stmt->close();
}

// Fetch areas for dropdown
$areas_result = $conn->query("SELECT area_id, area_name FROM area ORDER BY area_name ASC");

// Fetch waste types
$waste_types_result = $conn->query("SELECT waste_name FROM waste_type WHERE waste_name != 'all' ORDER BY waste_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Schedule | EcoCollect Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .form_container {
            max-width: 700px;
            margin: 80px auto;
            padding: 25px;
            background: #ffffff;
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
            background: #fafafa;
            transition: 0.3s;
        }
        .form_container input:focus,
        .form_container select:focus,
        .form_container textarea:focus {
            border-color: #388e3c;
            outline: none;
            background: #fff;
            box-shadow: 0 0 5px rgba(56,142,60,0.3);
        }
        .form_container textarea {
            resize: vertical;
            min-height: 70px;
        }
        .form_container .green_btn {
            background: linear-gradient(135deg, #388e3c, #66bb6a);
            color: #fff;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            grid-column: 1 / -1;
            margin-top: 15px;
        }
        .form_container .green_btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(56,142,60,0.3);
        }
        .dashboard_btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            text-align: center;
            margin-top: 15px;
            grid-column: 1 / -1;
        }
        .dashboard_btn:hover {
            background: #5a6268;
        }
        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
            grid-column: 1 / -1;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        /* Dropdown */
        .dropdown {
            position: relative;
        }
        .dropdown-toggle {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fafafa;
            cursor: pointer;
        }
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: none;
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
        }
        .dropdown-menu label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            cursor: pointer;
        }
        .dropdown-menu input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 8px;
        }
        /* Table styles for later pages */
        .schedule_table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }
        .schedule_table th,
        .schedule_table td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .schedule_table th {
            background: #388e3c;
            color: white;
        }
        @media (max-width: 768px) {
            .form_container form {
                grid-template-columns: 1fr;
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
            <div style="display: flex; gap: 10px;">
                <select name="hour" required style="flex: 1;">
                    <option value="">Hour</option>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $hour_12 ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
                <select name="minute" required style="flex: 1;">
                    <option value="">Minute</option>
                    <?php for ($i = 0; $i < 60; $i += 5): ?>
                        <option value="<?= sprintf('%02d', $i) ?>" <?= sprintf('%02d', $i) == $minute ? 'selected' : '' ?>><?= sprintf('%02d', $i) ?></option>
                    <?php endfor; ?>
                </select>
                <select name="ampm" required style="flex: 1;">
                    <option value="">AM/PM</option>
                    <option value="AM" <?= $ampm == 'AM' ? 'selected' : '' ?>>AM</option>
                    <option value="PM" <?= $ampm == 'PM' ? 'selected' : '' ?>>PM</option>
                </select>
            </div>
        </div>

        <div>
            <label>Waste Type:</label>
            <div class="dropdown">
                <div class="dropdown-toggle" id="dropdown-toggle">Select Waste Types</div>
                <div class="dropdown-menu" id="dropdown-menu">
                    <label>All <input type="checkbox" id="all" name="waste_type[]" value="all"></label>
                    <?php while ($waste_row = $waste_types_result->fetch_assoc()): ?>
                        <label>
                            <?= htmlspecialchars($waste_row['waste_name']); ?>
                            <input type="checkbox" name="waste_type[]" value="<?= htmlspecialchars($waste_row['waste_name']); ?>" <?= in_array($waste_row['waste_name'], $selected_waste_types) ? 'checked' : '' ?>>
                        </label>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="full-width">
            <label>Remarks (optional):</label>
            <textarea name="remarks" placeholder="Any extra information..."><?= htmlspecialchars($schedule['remarks'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="green_btn">✏️ Update Schedule</button>
    </form>
    <?php else: ?>
        <p style="text-align: center; color: #721c24; font-weight: bold;">This schedule has expired and cannot be edited.</p>
    <?php endif; ?>
</div>

<script>
    const dropdownToggle = document.getElementById('dropdown-toggle');
    const dropdownMenu = document.getElementById('dropdown-menu');
    const allCheckbox = document.getElementById('all');

    function updateToggleText() {
        const checked = Array.from(document.querySelectorAll('input[name="waste_type[]"]:checked')).map(c => c.value);
        dropdownToggle.textContent = checked.length ? checked.join(', ') : "Select Waste Types";
    }

    dropdownToggle.addEventListener('click', () => {
        dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', (e) => {
        if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.style.display = 'none';
        }
    });

    allCheckbox.addEventListener('change', () => {
        const checkboxes = document.querySelectorAll('input[name="waste_type[]"]:not(#all)');
        checkboxes.forEach(cb => cb.checked = allCheckbox.checked);
        updateToggleText();
    });

    document.querySelectorAll('input[name="waste_type[]"]:not(#all)').forEach(cb => {
        cb.addEventListener('change', () => {
            const allCheckboxes = document.querySelectorAll('input[name="waste_type[]"]:not(#all)');
            const checkedCheckboxes = document.querySelectorAll('input[name="waste_type[]"]:not(#all):checked');
            allCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0;
            updateToggleText();
        });
    });

    updateToggleText();
</script>
</body>
</html>

<?php $conn->close(); ?>
