<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Handle delete complaint
if (isset($_GET['delete_complaint'])) {
    $complaint_id = $_GET['delete_complaint'];

    $delete_stmt = $conn->prepare("DELETE FROM complaints WHERE id = ?");
    $delete_stmt->bind_param("i", $complaint_id);
    if ($delete_stmt->execute()) {
        $message = "Complaint deleted successfully.";
    } else {
        $message = "Error deleting complaint: " . $delete_stmt->error;
    }
}

$result = $conn->query("SELECT c.id, u.user_name, c.complaint_text, c.status, c.created_at
                        FROM complaints c
                        JOIN user u ON c.user_id = u.user_id
                        ORDER BY c.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | View Complaints</title>
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
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #ccc; text-align: left; }
        th { background: #66bb6a; color: #fff; }
        tr:hover { background: #f1f1f1; }
        .status { font-weight: bold; }
        .Pending { color: orange; }
        .Resolved { color: green; }
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
        <h2>All Complaints</h2>
        <?php if ($message != ""): ?>
            <div style="padding: 10px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; <?php echo strpos($message, 'Error') !== false ? 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' : 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <table>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Complaint</th>
                <th>Status</th>
                <th>Submitted At</th>
                <th>Actions</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['complaint_text']); ?></td>
                    <td class="status <?php echo $row['status']; ?>"><?php echo $row['status']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <a href="?delete_complaint=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this complaint?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
