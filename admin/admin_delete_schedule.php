<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$schedule_id = $_GET['schedule_id'] ?? null;
if (!$schedule_id) {
    header("Location: admin_view_schedules.php");
    exit();
}

// Fetch schedule status to check if expired
$status_stmt = $conn->prepare("SELECT status FROM schedule WHERE schedule_id = ?");
$status_stmt->bind_param("i", $schedule_id);
$status_stmt->execute();
$status_result = $status_stmt->get_result();
$schedule_status = $status_result->fetch_assoc()['status'];
$status_stmt->close();

// Delete the schedule
$stmt = $conn->prepare("DELETE FROM schedule WHERE schedule_id = ?");
$stmt->bind_param("i", $schedule_id);

if ($stmt->execute()) {
    if ($schedule_status === 'Expired') {
        $success = "✅ Expired schedule deleted successfully!";
    } else {
        $success = "✅ Schedule deleted successfully!";
    }
} else {
    $error = "❌ Error deleting schedule: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirect back with message
header("Location: admin_view_schedules.php?message=" . urlencode($success ?? $error));
exit();
?>
