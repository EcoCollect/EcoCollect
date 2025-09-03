<?php include('../db_connect/db_connect.php') ?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .status-pending {
            background-color: #A8E6CF; /* light green */
            color: #1B5E20;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 6px;
            display: inline-block;
        }
        .status-collected {
            background-color: #388E3C; /* dark green */
            color: #fff;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 6px;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-light">
<?php include('admin_navbar.php'); ?>
<div class="container mt-5" style="margin-top: 100px !important;">
    <h2 class="mb-4">Manage Waste Collection Requests</h2>

    <?php
    if (isset($_POST['update_status'])) {
        $request_id = $_POST['request_id'];
        $status = $_POST['status'];
        $conn->query("UPDATE request_collection SET status='$status' WHERE request_id=$request_id");
        echo "<div class='alert alert-success'>Status updated successfully!</div>";
    }

    $sql = "SELECT r.request_id, r.name, a.area_name, r.waste_type, r.weight, 
                   s.collection_date, s.collection_time, r.status, r.remarks 
            FROM request_collection r 
            JOIN area a ON r.area_id = a.area_id 
            JOIN schedule s ON r.schedule_id = s.schedule_id 
            ORDER BY r.request_id DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table class='table table-striped'>";
        echo "<tr><th>ID</th><th>Name</th><th>Area</th><th>Waste Type</th><th>Weight</th>
              <th>Date</th><th>Time</th><th>Status</th><th>Remarks</th><th>Action</th></tr>";
        while ($row = $result->fetch_assoc()) {
            // Status with colors
            $statusBadge = $row['status'] == 'Pending' 
                           ? "<span class='status-pending'>Pending</span>" 
                           : "<span class='status-collected'>Collected</span>";

            echo "<tr>
                    <td>{$row['request_id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['area_name']}</td>
                    <td>{$row['waste_type']}</td>
                    <td>{$row['weight']}</td>
                    <td>{$row['collection_date']}</td>
                    <td>{$row['collection_time']}</td>
                    <td>$statusBadge</td>
                    <td>{$row['remarks']}</td>
                    <td>
                        <form method='post' class='d-flex'>
                            <input type='hidden' name='request_id' value='{$row['request_id']}'>
                            <select name='status' class='form-select me-2'>
                                <option " . ($row['status']=='Pending'?'selected':'') . ">Pending</option>
                                <option " . ($row['status']=='Collected'?'selected':'') . ">Collected</option>
                            </select>
                            <button type='submit' name='update_status' class='btn btn-success btn-sm'>Update</button>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='alert alert-info'>No requests found.</div>";
    }
    ?>
</div>
</body>
</html>
