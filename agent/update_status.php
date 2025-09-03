<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

$agent_id = $_SESSION['agent_id'];

if (isset($_GET['id'])) {
    $request_id = intval($_GET['id']);

    // First, verify that the request belongs to the agent's area
    $stmt = $conn->prepare("
        SELECT r.area_id FROM request_collection r
        JOIN agent a ON r.area_id = a.area_id
        WHERE r.request_id = ? AND a.id = ?
    ");
    $stmt->bind_param("ii", $request_id, $agent_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the status to 'Collected'
        $update_stmt = $conn->prepare("UPDATE request_collection SET status = 'Collected' WHERE request_id = ?");
        $update_stmt->bind_param("i", $request_id);

        if ($update_stmt->execute()) {
            $message = "Request marked as collected successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating request: " . $update_stmt->error;
            $message_type = "error";
        }
        $update_stmt->close();
    } else {
        $message = "You do not have permission to update this request.";
        $message_type = "error";
    }
    $stmt->close();
} else {
    $message = "Invalid request.";
    $message_type = "error";
}

$conn->close();

// Redirect back to agent_view_schedule.php with message
header("Location: agent_view_schedule.php?message=" . urlencode($message) . "&type=" . $message_type);
exit();
?>
