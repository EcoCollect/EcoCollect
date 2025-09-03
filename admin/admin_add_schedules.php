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
    $waste_types = isset($_POST['waste_type']) ? $_POST['waste_type'] : [];
    $waste_type = implode(',', $waste_types);
    $remarks = $_POST['remarks'] ?? '';
    $status = $_POST['status'] ?? 'Scheduled'; // default

    $stmt = $conn->prepare("INSERT INTO schedule (area_id, collection_date, collection_time, waste_type, remarks, status) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $area_id, $collection_date, $collection_time, $waste_type, $remarks, $status);

    if ($stmt->execute()) {
        $success = "✅ Schedule added successfully!";
    } else {
        $error = "❌ Error adding schedule: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch areas
$areas_result = $conn->query("SELECT area_id, area_name FROM area ORDER BY area_name ASC");

// Fetch waste types
$waste_types_result = $conn->query("SELECT waste_name FROM waste_type WHERE waste_name != 'all' ORDER BY waste_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Schedule | EcoCollect Admin</title>
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
    <h2>Add New Collection Schedule</h2>

    <?php if (!empty($success)): ?>
        <div class="message success"><?= $success ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="message error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <div>
            <label>Area:</label>
            <select name="area_id" required>
                <option value="">-- Select Area --</option>
                <?php while ($row = $areas_result->fetch_assoc()): ?>
                    <option value="<?= $row['area_id']; ?>"><?= htmlspecialchars($row['area_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label>Collection Date:</label>
            <input type="date" name="collection_date" required>
        </div>

        <div>
            <label>Collection Time:</label>
            <input type="time" name="collection_time" required>
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
                            <input type="checkbox" name="waste_type[]" value="<?= htmlspecialchars($waste_row['waste_name']); ?>">
                        </label>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="full-width">
            <label>Remarks (optional):</label>
            <textarea name="remarks" placeholder="Any extra information..."></textarea>
        </div>

        <div>
            <label>Status:</label>
            <select name="status">
                <option value="Scheduled">Scheduled</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <button type="submit" class="green_btn">➕ Add Schedule</button>
        <a href="admin_dashboard.php" class="dashboard_btn">⬅ Back to Dashboard</a>
    </form>
</div>

<script>
    const dropdownToggle = document.getElementById('dropdown-toggle');
    const dropdownMenu = document.getElementById('dropdown-menu');

    dropdownToggle.addEventListener('click', () => {
        dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', (e) => {
        if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.style.display = 'none';
        }
    });

    document.querySelectorAll('input[name="waste_type[]"]').forEach(cb => {
        cb.addEventListener('change', () => {
            const checked = Array.from(document.querySelectorAll('input[name="waste_type[]"]:checked')).map(c => c.value);
            dropdownToggle.textContent = checked.length ? checked.join(', ') : "Select Waste Types";
        });
    });
</script>
</body>
</html>

<?php $conn->close(); ?>