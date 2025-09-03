<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Get agent ID from URL parameter
$agent_id = isset($_GET['agent_id']) ? (int)$_GET['agent_id'] : 0;

// If no agent ID provided, redirect back to agent list
if ($agent_id <= 0) {
    header("Location: admin_agent_list.php");
    exit();
}

// Fetch the specific agent data
$agent_query = "SELECT * FROM agent WHERE id = ?";
$stmt = $conn->prepare($agent_query);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$agent_result = $stmt->get_result();

if ($agent_result->num_rows == 0) {
    header("Location: admin_agent_list.php");
    exit();
}

$agent = $agent_result->fetch_assoc();

// Fetch all areas for dropdown
$areas_result = $conn->query("SELECT * FROM area ORDER BY area_name ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_agent'])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $area_id = $_POST["area_id"];
    $status = $_POST["status"];

    $sql = "UPDATE agent SET name=?, email=?, phone=?, address=?, area_id=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisi", $name, $email, $phone, $address, $area_id, $status, $agent_id);

    if ($stmt->execute()) {
        $message = "Agent updated successfully.";
        // Refresh agent data after update
        $agent_result = $conn->query("SELECT * FROM agent WHERE id = $agent_id");
        $agent = $agent_result->fetch_assoc();
    } else {
        $message = "Error: " . $stmt->error;
    }
}

// Handle delete agent
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_agent'])) {
    if ($agent['status'] == 'inactive') {
        // Allow deletion for inactive agents
        $delete_stmt = $conn->prepare("DELETE FROM agent WHERE id = ?");
        $delete_stmt->bind_param("i", $agent_id);
        if ($delete_stmt->execute()) {
            header("Location: admin_agent_list.php?message=Agent deleted successfully.");
            exit();
        } else {
            $message = "Error deleting agent: " . $delete_stmt->error;
        }
    } else {
        // Check if agent has pending pickups for active agents
        $check_pickups = $conn->prepare("SELECT COUNT(*) as count FROM request_collection WHERE area_id = (SELECT area_id FROM agent WHERE id = ?) AND status IN ('Pending', 'In Progress')");
        $check_pickups->bind_param("i", $agent_id);
        $check_pickups->execute();
        $pickup_count = $check_pickups->get_result()->fetch_assoc()['count'];

        if ($pickup_count > 0) {
            $message = "Cannot delete agent. There are $pickup_count pending request(s) in this agent's area.";
        } else {
            $delete_stmt = $conn->prepare("DELETE FROM agent WHERE id = ?");
            $delete_stmt->bind_param("i", $agent_id);
            if ($delete_stmt->execute()) {
                header("Location: admin_agent_list.php?message=Agent deleted successfully.");
                exit();
            } else {
                $message = "Error deleting agent: " . $delete_stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Edit Agent</title>
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
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-container input, .form-container select, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container button {
            background-color: #388e3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container button:hover {
            background-color: #2e7d32;
        }
        .form-container .cancel-btn {
            background-color: #6c757d;
            margin-left: 10px;
        }
        .form-container .cancel-btn:hover {
            background-color: #5a6268;
        }
        .button-group {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }
        .message {
            color: green;
            margin-bottom: 15px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="content">
    <div class="form-container">
        <h2>Edit Agent: <?php echo htmlspecialchars($agent['name']); ?></h2>
        <?php if ($message != ""): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">

            <label>Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($agent['name']); ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($agent['email']); ?>" required>

            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($agent['phone']); ?>" required>

            <label>Address:</label>
            <textarea name="address" rows="3"><?php echo htmlspecialchars($agent['address']); ?></textarea>

            <label>Assigned Area:</label>
            <select name="area_id" required>
                <option value="">Select Area</option>
                <?php while($area = $areas_result->fetch_assoc()): ?>
                    <option value="<?php echo $area['area_id']; ?>" <?php echo ($area['area_id'] == $agent['area_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($area['area_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Status:</label>
            <select name="status" required>
                <option value="active" <?php echo ($agent['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo ($agent['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>

            <div class="button-group">
                <button type="submit" name="update_agent">Update Agent</button>
                <button type="submit" name="delete_agent" onclick="return confirm('Are you sure you want to delete this agent? This action cannot be undone.')"
                    style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
                    Delete Agent
                </button>
                <a href="admin_agent_list.php" class="cancel-btn" style="text-decoration: none; padding: 10px 20px; display: inline-block;">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
