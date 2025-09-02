<?php
include('../db_connect/db_connect.php');

// Check if request_id is passed
if (!isset($_GET['id'])) {
    die("Invalid Request ID.");
}

$request_id = intval($_GET['id']);
$message = "";

// If form is submitted, update the status and remarks
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    $update_sql = "UPDATE request_collection SET status = ?, remarks = ? WHERE request_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $status, $remarks, $request_id);

    if ($stmt->execute()) {
        $message = "✅ Request updated successfully!";
    } else {
        $message = "❌ Error updating request: " . $conn->error;
    }
    $stmt->close();
}

// Fetch the request details
$sql = "SELECT r.*, a.area_name, s.collection_date, s.collection_time 
        FROM request_collection r
        JOIN area a ON r.area_id = a.area_id
        JOIN schedule s ON r.schedule_id = s.schedule_id
        WHERE r.request_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

if (!$request) {
    die("Request not found.");
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Request - EcoCollect</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Edit Request</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label"><b>User Name:</b></label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($request['name']) ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label"><b>Area:</b></label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($request['area_name']) ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label"><b>Waste Type:</b></label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($request['waste_type']) ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label"><b>Weight (kg):</b></label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($request['weight']) ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label"><b>Scheduled Date & Time:</b></label>
            <input type="text" class="form-control" value="<?= $request['collection_date'] . ' ' . $request['collection_time'] ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="Pending" <?= ($request['status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                <option value="Collected" <?= ($request['status'] == 'Collected') ? 'selected' : '' ?>>Collected</option>
                <option value="In Progress" <?= ($request['status'] == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                <option value="Rejected" <?= ($request['status'] == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="3"><?= htmlspecialchars($request['remarks']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Update Request</button>
        <a href="admin_view_requests.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
