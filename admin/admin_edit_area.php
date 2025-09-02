<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_area'])) {
    $area_id = $_POST["area_id"];
    $area_name = $_POST["area_name"];
    $district = $_POST["district"];
    $state = $_POST["state"];
    $pin_code = $_POST["pin_code"];
    $area_description = $_POST["area_description"];

    $sql = "UPDATE area SET area_name=?, district=?, state=?, pin_code=?, area_description=? WHERE area_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $area_name, $district, $state, $pin_code, $area_description, $area_id);

    if ($stmt->execute()) {
        $message = "Area updated successfully.";
    } else {
        $message = "Error: " . $stmt->error;
    }
}

$areas_result = $conn->query("SELECT * FROM area ORDER BY area_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Edit Area</title>
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
        .edit-btn {
            background-color: #388e3c;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .edit-btn:hover {
            background-color: #2e7d32;
        }
        .form-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-container input, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container button {
            background-color: #388e3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container button:hover {
            background-color: #2e7d32;
        }
        .message {
            color: green;
            margin-bottom: 15px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="content">
    <div class="table-container">
        <h2>Edit Areas</h2>
        <?php if ($message != "") echo "<p class='message'>$message</p>"; ?>
        <table>
            <thead>
                <tr>
                    <th>Area ID</th>
                    <th>Area Name</th>
                    <th>District</th>
                    <th>State</th>
                    <th>Pin Code</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $areas_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['area_id']) ?></td>
                    <td><?= htmlspecialchars($row['area_name']) ?></td>
                    <td><?= htmlspecialchars($row['district']) ?></td>
                    <td><?= htmlspecialchars($row['state']) ?></td>
                    <td><?= htmlspecialchars($row['pin_code']) ?></td>
                    <td><?= htmlspecialchars($row['area_description']) ?></td>
                    <td>
                        <button class="edit-btn" onclick="editArea(<?= $row['area_id'] ?>, '<?= htmlspecialchars($row['area_name']) ?>', '<?= htmlspecialchars($row['district']) ?>', '<?= htmlspecialchars($row['state']) ?>', '<?= htmlspecialchars($row['pin_code']) ?>', '<?= htmlspecialchars($row['area_description']) ?>')">Edit</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="form-container" id="editForm" style="display: none;">
        <h2>Edit Area</h2>
        <form method="POST">
            <input type="hidden" name="area_id" id="edit_area_id">
            <label>Area Name:</label>
            <input type="text" name="area_name" id="edit_area_name" required>

            <label>District:</label>
            <input type="text" name="district" id="edit_district">

            <label>State:</label>
            <input type="text" name="state" id="edit_state">

            <label>Pin Code:</label>
            <input type="text" name="pin_code" id="edit_pin_code">

            <label>Area Description:</label>
            <textarea name="area_description" id="edit_area_description" rows="4"></textarea>

            <button type="submit" name="update_area">Update Area</button>
        </form>
    </div>
</div>

<script>
function editArea(id, name, district, state, pin, desc) {
    document.getElementById('edit_area_id').value = id;
    document.getElementById('edit_area_name').value = name;
    document.getElementById('edit_district').value = district;
    document.getElementById('edit_state').value = state;
    document.getElementById('edit_pin_code').value = pin;
    document.getElementById('edit_area_description').value = desc;
    document.getElementById('editForm').style.display = 'block';
    window.scrollTo(0, document.body.scrollHeight);
}
</script>
</body>
</html>
