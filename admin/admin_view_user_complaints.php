<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
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
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="content">
    <div class="table-container">
        <h2>All Complaints</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Complaint</th>
                <th>Status</th>
                <th>Submitted At</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['complaint_text']); ?></td>
                    <td class="status <?php echo $row['status']; ?>"><?php echo $row['status']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
