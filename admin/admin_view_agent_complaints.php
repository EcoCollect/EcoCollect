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

// Handle resolve action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve'])) {
    $complaint_id = (int)$_POST['complaint_id'];
    $update_stmt = $conn->prepare("UPDATE agent_complaints SET status='Resolved' WHERE id=?");
    $update_stmt->bind_param("i", $complaint_id);
    $update_stmt->execute();
    $update_stmt->close();
    // Refresh the page
    header("Location: admin_view_agent_complaints.php");
    exit();
}
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
        .resolve-btn {
            background: #388e3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        .resolve-btn:hover {
            background: #2e7d32;
        }
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
                    <th>Action</th>
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
                    <td>
                        <?php if ($row['status'] === 'Pending'): ?>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="complaint_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="resolve" class="resolve-btn">Resolve</button>
                            </form>
                        <?php else: ?>
                            Resolved
                        <?php endif; ?>
                    </td>
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
