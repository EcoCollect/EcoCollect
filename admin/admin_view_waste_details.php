<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch waste details from request_collection for collected and pending requests
$result = $conn->query("SELECT rc.request_id, rc.name, rc.waste_type, rc.weight, rc.status, rc.remarks, rc.created_at, a.area_name
                        FROM request_collection rc
                        LEFT JOIN area a ON rc.area_id = a.area_id
                        WHERE rc.status IN ('Collected', 'Pending')
                        ORDER BY rc.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Waste Details</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }
        th {
            background: #66bb6a;
            color: #fff;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .status {
            font-weight: bold;
        }
        .collected {
            color: green;
        }
        .pending {
            color: orange;
        }
        .in-progress {
            color: blue;
        }
        .rejected {
            color: red;
        }
        .btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
        }
        .btn-view {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>

<?php
$agg_result = $conn->query("SELECT waste_type, status, SUM(weight) as total_weight FROM request_collection WHERE status IN ('Collected', 'Pending') AND waste_type NOT IN ('all', 'textile') GROUP BY waste_type, status");
$waste_data = [];
while ($row = $agg_result->fetch_assoc()) {
    $waste_data[$row['waste_type']][$row['status']] = (float)$row['total_weight'];
}
// Prepare data arrays for chart
// Sort waste types by total weight descending (collected + pending)
$waste_types = array_keys($waste_data);
$waste_totals = [];
foreach ($waste_types as $type) {
    $collected = isset($waste_data[$type]['Collected']) ? $waste_data[$type]['Collected'] : 0;
    $pending = isset($waste_data[$type]['Pending']) ? $waste_data[$type]['Pending'] : 0;
    $waste_totals[$type] = $collected + $pending;
}
arsort($waste_totals);
$sorted_waste_types = array_keys($waste_totals);

$collected_data = [];
$pending_data = [];
foreach ($sorted_waste_types as $type) {
    $collected_data[] = isset($waste_data[$type]['Collected']) ? $waste_data[$type]['Collected'] : 0;
    $pending_data[] = isset($waste_data[$type]['Pending']) ? $waste_data[$type]['Pending'] : 0;
}
?>

<div class="content">
    <div class="chart-container" style="max-width: 600px; margin: 40px auto;">
        <h2>Waste Quantity by Type and Status</h2>
        <canvas id="wasteChart" style="height: 300px;"></canvas>
    </div>

    <div class="table-container" style="max-width: 800px; margin: 20px auto;">
        <h3>Waste Details by Type</h3>
        <table>
            <thead>
                <tr>
                    <th>Waste Type</th>
                    <th>Collected (kg)</th>
                    <th>Pending (kg)</th>
                    <th>Total (kg)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sorted_waste_types as $type): ?>
                <tr>
                    <td><?php echo htmlspecialchars($type); ?></td>
                    <td><?php echo isset($waste_data[$type]['Collected']) ? number_format($waste_data[$type]['Collected'], 2) : '0.00'; ?></td>
                    <td><?php echo isset($waste_data[$type]['Pending']) ? number_format($waste_data[$type]['Pending'], 2) : '0.00'; ?></td>
                    <td><?php echo number_format(($waste_data[$type]['Collected'] ?? 0) + ($waste_data[$type]['Pending'] ?? 0), 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('wasteChart').getContext('2d');
const wasteChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($sorted_waste_types) ?>,
        datasets: [
            {
                label: 'Collected',
                data: <?= json_encode($collected_data) ?>,
                backgroundColor: [
                    '#4caf50',
                    '#81c784',
                    '#a5d6a7',
                    '#c8e6c9',
                    '#66bb6a',
                    '#388e3c',
                    '#2e7d32',
                    '#1b5e20',
                    '#43a047',
                    '#388e3c'
                ],
                borderColor: '#2e7d32',
                borderWidth: 1,
                barPercentage: 0.5,
                categoryPercentage: 0.5
            },
            {
                label: 'Pending',
                data: <?= json_encode($pending_data) ?>,
                backgroundColor: [
                    '#ff9800',
                    '#ffb74d',
                    '#ffcc80',
                    '#ffe0b2',
                    '#ffca28',
                    '#f57c00',
                    '#ef6c00',
                    '#e65100',
                    '#ffa726',
                    '#fb8c00'
                ],
                borderColor: '#ef6c00',
                borderWidth: 1,
                barPercentage: 0.5,
                categoryPercentage: 0.5
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Waste Collection Overview'
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Waste Type'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Weight (kg)'
                }
            }
        },
        animation: {
            duration: 1500,
            easing: 'easeOutQuart'
        }
    }
});
</script>
</body>
</html>
