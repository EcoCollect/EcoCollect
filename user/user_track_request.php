<?php
session_start();
include("../db_connect/db_connect.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../user_login.php");
    exit();
}

$user_name = $_SESSION['user_name'];
include("user_navbar.php");

// Filters
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'All';

// Base query
$query = "SELECT request_id, waste_type, weight, status, created_at 
          FROM request_collection 
          WHERE name = ?";

$params = [$user_name];
$types = "s";

// Add date filter
if (!empty($date_filter)) {
    $query .= " AND DATE(created_at) = ?";
    $types .= "s";
    $params[] = $date_filter;
}

// Add status filter
if (!empty($status_filter) && $status_filter != "All") {
    $query .= " AND status = ?";
    $types .= "s";
    $params[] = $status_filter;
}

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Track Requests</title>
    <style>
        /* ========== General Body ========== */
        body {
            font-family: Arial, sans-serif;
            background-color: #e8f5e9;
            margin: 0;
            padding: 0;
        }
        .content-wrapper {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }
        .track-container {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .track-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #388e3c;
        }

        /* ========== Filter Form ========== */
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 20px;
        }
        .filter-form label {
            font-weight: bold;
            margin-right: 5px;
        }
        .filter-form input,
        .filter-form select {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .filter-form button,
        .filter-form .reset-btn {
            padding: 7px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            color: #ffffff;
            background-color: #66bb6a;
            transition: 0.3s;
            text-decoration: none;
        }
        .filter-form button:hover,
        .filter-form .reset-btn:hover {
            background-color: #388e3c;
        }

        /* ========== Requests Table ========== */
        .requests-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            font-size: 14px;
        }
        .requests-table th,
        .requests-table td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }
        .requests-table th {
            background-color: #388e3c;
            color: #ffffff;
            font-weight: bold;
        }
        .requests-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .requests-table tr:nth-child(odd) {
            background-color: #f0f7f0;
        }
        .requests-table tr:hover {
            background-color: #d9efd9;
        }

        /* ========== Status Badges ========== */
        .status {
            display: inline-block;
            min-width: 90px;
            padding: 6px 12px;
            border-radius: 12px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            font-size: 13px;
        }
        .status.pending    { background-color: #f6a644; color: #fff; }
        /* .status.scheduled  { background-color: #1e88e5; color: #fff; } */
        /* .status.inprogress { background-color: #BDD7B0; color: #333; } */
        .status.collected  { background-color: #388e3c; color: #fff; }
        /* .status.rejected   { background-color: #e53935; color: #fff; } */

        /* ========== Responsive Table ========== */
        @media (max-width: 768px) {
            .requests-table {
                display: block;
                overflow-x: auto;
                width: 100%;
            }
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-form input,
            .filter-form select,
            .filter-form button,
            .filter-form .reset-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="content-wrapper">
    <div class="track-container">
        <h2>Track Your Requests</h2>

        <!-- Filter Form -->
        <form method="GET" class="filter-form">
            <label for="date">Filter by Date:</label>
            <input type="date" name="date" id="date" value="<?= htmlspecialchars($date_filter) ?>">

            <label for="status">Filter by Status:</label>
            <select name="status" id="status">
                <option value="All" <?= $status_filter=="All"?'selected':'' ?>>All</option>
                <option value="Pending" <?= $status_filter=="Pending"?'selected':'' ?>>Pending</option>
                <!-- <option value="Scheduled" <?= $status_filter=="Scheduled"?'selected':'' ?>>Scheduled</option> -->
                <option value="Collected" <?= $status_filter=="Collected"?'selected':'' ?>>Collected</option>
                <!-- <option value="Rejected" <?= $status_filter=="Rejected"?'selected':'' ?>>Rejected</option> -->
            </select>

            <button type="submit">Search</button>
            <a href="user_track_request.php" class="reset-btn">Reset</a>
        </form>

        <!-- Requests Table -->
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Waste Type</th>
                    <th>Weight (kg)</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['request_id'] ?></td>
                            <td><?= htmlspecialchars($row['waste_type']) ?></td>
                            <td><?= $row['weight'] ?></td>
                            <td>
                                <span class="status <?= strtolower($row['status']) ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td><?= $row['created_at'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
?>

</body>
</html>
