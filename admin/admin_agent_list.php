<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Handle delete agent
if (isset($_GET['delete_agent'])) {
    $agent_id = $_GET['delete_agent'];

    // Check agent status
    $agent_status_query = $conn->prepare("SELECT status FROM agent WHERE id = ?");
    $agent_status_query->bind_param("i", $agent_id);
    $agent_status_query->execute();
    $agent_status = $agent_status_query->get_result()->fetch_assoc()['status'];

    if ($agent_status == 'inactive') {
        // Allow deletion for inactive agents
        $delete_stmt = $conn->prepare("DELETE FROM agent WHERE id = ?");
        $delete_stmt->bind_param("i", $agent_id);
        if ($delete_stmt->execute()) {
            $message = "Agent deleted successfully.";
        } else {
            $message = "Error deleting agent: " . $delete_stmt->error;
        }
    } else {
        // Check if agent has pending pickups for active agents
        // Changed table name from 'agent_pickups' to 'request_collection' as per database schema
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
                $message = "Agent deleted successfully.";
            } else {
                $message = "Error deleting agent: " . $delete_stmt->error;
            }
        }
    }
}

$result = $conn->query("SELECT id, email, name, phone, address, status FROM agent ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Agent List</title>
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
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }
        th {
            background: #66bb6a;
            color: #fff;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .status {
            font-weight: bold;
        }
        .active {
            color: green;
        }
        .inactive {
            color: red;
        }
        .btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
        }
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="content">
    <div class="table-container">
        <h2>Registered Agents</h2>
        <?php if ($message != ""): ?>
            <div style="padding: 10px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; <?php echo strpos($message, 'Error') !== false || strpos($message, 'Cannot') !== false ? 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' : 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Agent ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td class="status <?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <a href="admin_edit_agent.php?agent_id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="?delete_agent=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this agent?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No registered agents found.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
