<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

$agent_id = $_SESSION['agent_id'];

// Fetch complaints submitted by this agent
$stmt = $conn->prepare("SELECT id, complaint_text, status, created_at FROM agent_complaints WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | My Complaints</title>
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
<?php include('agent_navbar.php'); ?>
<div class="content">
    <div class="table-container">
        <h2>My Complaints & Feedback</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Complaint</th>
                <th>Status</th>
                <th>Submitted At</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['complaint_text']); ?></td>
                    <td class="status <?php echo $row['status']; ?>"><?php echo $row['status']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows === 0): ?>
                <tr><td colspan="4">No complaints found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
