<?php
session_start();
include('../db_connect/db_connect.php');

// Check agent login
if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

$agent_id = $_SESSION['agent_id'];

// Fetch only this agent's complaints
$stmt = $conn->prepare("SELECT id, complaint_text, status, admin_feedback, created_at 
                        FROM agent_complaints 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>EcoCollect | My Complaints</title>
<style>
    body { margin:0; font-family: Arial, sans-serif; background:#f0f2f5; }
    .content { margin-top: 80px; padding: 20px; }
    .table-container { max-width: 1000px; margin: auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 8px rgba(0,0,0,0.1); }
    table { width:100%; border-collapse: collapse; }
    th, td { padding: 12px; border-bottom:1px solid #ccc; text-align:left; }
    th { background:#42a5f5; color:#fff; }
    tr:hover { background:#f9f9f9; }
    .status { font-weight:bold; }
    .Pending { color:orange; }
    .Resolved { color:green; }
    .complaint-text { max-width: 400px; word-wrap: break-word; }
    .feedback { color:#555; font-style: italic; }
</style>
</head>
<body>
<?php include('agent_navbar.php'); ?>

<div class="content">
<div class="table-container">
<h2>My Complaints & Feedback</h2>

<?php if ($result->num_rows > 0): ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Complaint</th>
            <th>Status</th>
            <th>Admin Feedback</th>
            <th>Submitted At</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']); ?></td>
            <td class="complaint-text"><?= htmlspecialchars($row['complaint_text']); ?></td>
            <td class="status <?= $row['status']; ?>"><?= htmlspecialchars($row['status']); ?></td>
            <td class="feedback">
                <?php 
                    if ($row['status'] === 'Resolved' && !empty($row['admin_feedback'])) {
                        echo htmlspecialchars($row['admin_feedback']);
                    } else {
                        echo '<span style="color:gray;">No feedback yet</span>';
                    }
                ?>
            </td>
            <td><?= htmlspecialchars($row['created_at']); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
    <p>You have not submitted any complaints yet.</p>
<?php endif; ?>
</div>
</div>
</body>
</html>
