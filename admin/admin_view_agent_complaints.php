<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle resolve action with feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve'])) {
    $complaint_id = (int)$_POST['complaint_id'];
    $feedback = trim($_POST['admin_feedback']);

    $update_stmt = $conn->prepare("UPDATE agent_complaints SET status='Resolved', admin_feedback=? WHERE id=?");
    $update_stmt->bind_param("si", $feedback, $complaint_id);
    $update_stmt->execute();
    $update_stmt->close();

    header("Location: admin_view_agent_complaints.php");
    exit();
}

$result = $conn->query("SELECT ac.id, a.name as agent_name, ac.complaint_text, ac.status, ac.admin_feedback, ac.created_at
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
    body { margin: 0; font-family: Arial, sans-serif; background: #f0f2f5; }
    .content { margin-top: 80px; padding: 20px; }
    .table-container { max-width: 1200px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; border-bottom: 1px solid #ccc; text-align: left; }
    th { background: #66bb6a; color: #fff; }
    tr:hover { background: #f1f1f1; }
    .status { font-weight: bold; }
    .Pending { color: orange; }
    .Resolved { color: green; }
    .complaint-text { max-width: 400px; word-wrap: break-word; }
    .resolve-btn { background: #388e3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
    .resolve-btn:hover { background: #2e7d32; }
    .feedback-input { width: 100%; margin-top: 5px; }
</style>
<script>
function showFeedbackForm(id){
    document.getElementById('feedback-form-'+id).style.display = 'block';
}
</script>
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
            <td><?= htmlspecialchars($row['id']); ?></td>
            <td><?= htmlspecialchars($row['agent_name']); ?></td>
            <td class="complaint-text"><?= htmlspecialchars($row['complaint_text']); ?></td>
            <td class="status <?= $row['status']; ?>"><?= htmlspecialchars($row['status']); ?></td>
            <td><?= htmlspecialchars($row['created_at']); ?></td>
            <td>
                <?php if ($row['status'] === 'Pending'): ?>
                    <button type="button" class="resolve-btn" onclick="showFeedbackForm(<?= $row['id']; ?>)">Resolve</button>
                    <form method="post" style="display:none; margin-top:5px;" id="feedback-form-<?= $row['id']; ?>">
                        <input type="hidden" name="complaint_id" value="<?= $row['id']; ?>">
                        <textarea name="admin_feedback" class="feedback-input" placeholder="Enter feedback for agent" required></textarea>
                        <br>
                        <button type="submit" name="resolve" class="resolve-btn" style="margin-top:5px;">Submit</button>
                    </form>
                <?php else: ?>
                    ✅ Resolved<br>
                    <small><b>Feedback:</b> <?= htmlspecialchars($row['admin_feedback']); ?></small>
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
