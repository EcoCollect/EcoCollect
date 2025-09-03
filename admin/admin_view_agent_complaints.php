<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$result = $conn->query("SELECT ac.id, a.name as agent_name, ac.complaint_text, ac.status, ac.created_at
                        FROM agent_complaints ac
                        JOIN agent a ON ac.user_id = a.id
                        ORDER BY ac.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Agent Complaints</title>
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
            max-width: 1200px;
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
        .complaint-text { max-width: 400px; word-wrap: break-word; }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="content">
    <div class="table-container">
        <h2>Agent Complaints & Feedback</h2>

        <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Agent Name</th>
                    <th>Complaint/Feedback</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['agent_name']); ?></td>
                    <td class="complaint-text"><?php echo htmlspecialchars($row['complaint_text']); ?></td>
                    <td class="status <?php echo $row['status']; ?>"><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No agent complaints found.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
