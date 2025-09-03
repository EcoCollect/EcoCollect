<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Handle delete area
if (isset($_GET['delete_area'])) {
    $area_id = $_GET['delete_area'];

    // Check if area has agents assigned
    $check_agents = $conn->prepare("SELECT COUNT(*) as count FROM agent WHERE area_id = ?");
    $check_agents->bind_param("i", $area_id);
    $check_agents->execute();
    $agent_count = $check_agents->get_result()->fetch_assoc()['count'];

    if ($agent_count > 0) {
        $message = "Cannot delete area. There are $agent_count agent(s) assigned to this area.";
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM area WHERE area_id = ?");
        $delete_stmt->bind_param("i", $area_id);
        if ($delete_stmt->execute()) {
            $message = "Area deleted successfully.";
        } else {
            $message = "Error deleting area: " . $delete_stmt->error;
        }
    }
}

// Fetch all areas with agent count
$areas_query = "SELECT a.*, COUNT(ag.id) as agent_count
                FROM area a
                LEFT JOIN agent ag ON a.area_id = ag.area_id
                GROUP BY a.area_id
                ORDER BY a.area_name ASC";
$areas_result = $conn->query($areas_query);

// Fetch agents grouped by area
$agents_by_area = [];
$agents_query = "SELECT ag.*, a.area_name
                 FROM agent ag
                 JOIN area a ON ag.area_id = a.area_id
                 ORDER BY a.area_name, ag.name";
$agents_result = $conn->query($agents_query);

while ($agent = $agents_result->fetch_assoc()) {
    $area_name = $agent['area_name'];
    if (!isset($agents_by_area[$area_name])) {
        $agents_by_area[$area_name] = [];
    }
    $agents_by_area[$area_name][] = $agent;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | View Areas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f0f2f5;
        }
        .content {
            margin-top: 80px;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section h2 {
            color: #388e3c;
            margin-top: 0;
            border-bottom: 2px solid #66bb6a;
            padding-bottom: 10px;
        }
        .area-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: transform 0.3s;
        }
        .area-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .area-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .area-title {
            font-size: 18px;
            font-weight: bold;
            color: #388e3c;
        }
        .area-stats {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: #666;
        }
        .area-details {
            color: #555;
            margin-bottom: 10px;
        }
        .agent-list {
            background: white;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
        }
        .agent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .agent-item:last-child {
            border-bottom: none;
        }
        .agent-info {
            flex: 1;
        }
        .agent-name {
            font-weight: bold;
            color: #333;
        }
        .agent-details {
            font-size: 12px;
            color: #666;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-edit:hover {
            background-color: #e0a800;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .no-agents {
            color: #666;
            font-style: italic;
            padding: 10px 0;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 5px 0;
            color: #388e3c;
            font-size: 24px;
        }
        .stat-card p {
            margin: 0;
            color: #666;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="content">
    <div class="container">
        <h1 style="text-align: center; color: #388e3c; margin-bottom: 30px;">Area Management</h1>

        <?php if ($message != ""): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false || strpos($message, 'Cannot') !== false ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Summary Statistics -->
        <div class="summary-stats">
            <div class="stat-card">
                <h3><?php echo $areas_result->num_rows; ?></h3>
                <p>Total Areas</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $agents_result->num_rows; ?></h3>
                <p>Total Agents</p>
            </div>
            <div class="stat-card">
                <h3><?php
                    $active_agents = 0;
                    mysqli_data_seek($agents_result, 0); // Reset pointer
                    while ($agent = $agents_result->fetch_assoc()) {
                        if ($agent['status'] == 'active') $active_agents++;
                    }
                    echo $active_agents;
                ?></h3>
                <p>Active Agents</p>
            </div>
        </div>

        <!-- Areas and Agents List -->
        <div class="section">
            <h2>Areas & Assigned Agents</h2>

            <?php if ($areas_result->num_rows > 0): ?>
                <?php while($area = $areas_result->fetch_assoc()): ?>
                    <div class="area-card">
                        <div class="area-header">
                            <div class="area-title"><?php echo htmlspecialchars($area['area_name']); ?></div>
                            <div class="area-stats">
                                <span>📍 <?php echo htmlspecialchars($area['district']); ?>, <?php echo htmlspecialchars($area['state']); ?></span>
                                <span>📮 <?php echo htmlspecialchars($area['pin_code']); ?></span>
                                <span>👥 <?php echo $area['agent_count']; ?> Agent(s)</span>
                            </div>
                        </div>

                        <div class="area-details">
                            <?php echo htmlspecialchars($area['area_description']); ?>
                        </div>

                        <div class="action-buttons">
                            <a href="admin_edit_area.php?edit_id=<?php echo $area['area_id']; ?>" class="btn btn-edit">Edit Area</a>
                            <a href="?delete_area=<?php echo $area['area_id']; ?>" class="btn btn-delete"
                               onclick="return confirm('Are you sure you want to delete this area?')">Delete Area</a>
                        </div>

                        <!-- Agents in this area -->
                        <div class="agent-list">
                            <h4 style="margin: 0 0 10px 0; color: #555;">Assigned Agents:</h4>
                            <?php if (isset($agents_by_area[$area['area_name']]) && count($agents_by_area[$area['area_name']]) > 0): ?>
                                <?php foreach($agents_by_area[$area['area_name']] as $agent): ?>
                                    <div class="agent-item">
                                        <div class="agent-info">
                                            <div class="agent-name"><?php echo htmlspecialchars($agent['name']); ?></div>
                                            <div class="agent-details">
                                                📧 <?php echo htmlspecialchars($agent['email']); ?> |
                                                📱 <?php echo htmlspecialchars($agent['phone']); ?> |
                                                📍 <?php echo htmlspecialchars($agent['address']); ?>
                                            </div>
                                        </div>
                                        <span class="status-badge status-<?php echo $agent['status']; ?>">
                                            <?php echo ucfirst($agent['status']); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-agents">No agents assigned to this area</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">No areas found. <a href="admin_add_area.php" style="color: #388e3c;">Add your first area</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
