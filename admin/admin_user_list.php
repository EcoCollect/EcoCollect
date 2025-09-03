<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Handle delete user
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    // Get user's name first
    $get_user = $conn->prepare("SELECT user_name FROM user WHERE user_id = ?");
    $get_user->bind_param("i", $user_id);
    $get_user->execute();
    $user_result = $get_user->get_result();

    if ($user_result->num_rows > 0) {
        $user_name = $user_result->fetch_assoc()['user_name'];

        // Check if user has pending requests using name instead of user_id
        $check_requests = $conn->prepare("SELECT COUNT(*) as count FROM request_collection WHERE name = ? AND status != 'Collected'");
        $check_requests->bind_param("s", $user_name);
        $check_requests->execute();
        $request_count = $check_requests->get_result()->fetch_assoc()['count'];

        if ($request_count > 0) {
            $message = "Cannot delete user. There are $request_count pending request(s) assigned to this user.";
        } else {
            $delete_stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
            $delete_stmt->bind_param("i", $user_id);
            if ($delete_stmt->execute()) {
                $message = "User deleted successfully.";
            } else {
                $message = "Error deleting user: " . $delete_stmt->error;
            }
        }
        $check_requests->close();
    } else {
        $message = "User not found.";
    }
    $get_user->close();
}

$result = $conn->query("SELECT user_id, user_name, user_email, user_phone, user_address FROM user ORDER BY user_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | User List</title>
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
        .btn-delete {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="content">
    <div class="table-container">
        <h2>Registered Users</h2>
        <?php if ($message != ""): ?>
            <div style="padding: 10px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; <?php echo strpos($message, 'Error') !== false || strpos($message, 'Cannot') !== false ? 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' : 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                    <td><?= htmlspecialchars($row['user_email']) ?></td>
                    <td><?= htmlspecialchars($row['user_phone']) ?></td>
                    <td><?= htmlspecialchars($row['user_address']) ?></td>
                    <td>
                        <a href="?delete_user=<?= $row['user_id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No registered users found.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
