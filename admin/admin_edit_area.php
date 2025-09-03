<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Get area ID from URL parameter
$area_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;

// If no area ID provided, redirect back to view areas
if ($area_id <= 0) {
    header("Location: admin_view_area.php");
    exit();
}

// Fetch the specific area data
$area_query = "SELECT * FROM area WHERE area_id = ?";
$stmt = $conn->prepare($area_query);
$stmt->bind_param("i", $area_id);
$stmt->execute();
$area_result = $stmt->get_result();

if ($area_result->num_rows == 0) {
    header("Location: admin_view_area.php");
    exit();
}

$area = $area_result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_area'])) {
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
        // Refresh area data after update
        $area_result = $conn->query("SELECT * FROM area WHERE area_id = $area_id");
        $area = $area_result->fetch_assoc();
    } else {
        $message = "Error: " . $stmt->error;
    }
}
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
        .form-container .cancel-btn {
            background-color: #6c757d;
            margin-left: 10px;
        }
        .form-container .cancel-btn:hover {
            background-color: #5a6268;
        }
        .button-group {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
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
    <div class="form-container">
        <h2>Edit Area: <?php echo htmlspecialchars($area['area_name']); ?></h2>
        <?php if ($message != "") echo "<p class='message'>$message</p>"; ?>

        <form method="POST">
            <input type="hidden" name="area_id" value="<?php echo $area['area_id']; ?>">

            <label>Area Name:</label>
            <input type="text" name="area_name" value="<?php echo htmlspecialchars($area['area_name']); ?>" required>

            <label>District:</label>
            <input type="text" name="district" value="<?php echo htmlspecialchars($area['district']); ?>">

            <label>State:</label>
            <input type="text" name="state" value="<?php echo htmlspecialchars($area['state']); ?>">

            <label>Pin Code:</label>
            <input type="text" name="pin_code" value="<?php echo htmlspecialchars($area['pin_code']); ?>">

            <label>Area Description:</label>
            <textarea name="area_description" rows="4"><?php echo htmlspecialchars($area['area_description']); ?></textarea>

            <div class="button-group">
                <button type="submit" name="update_area">Update Area</button>
                <a href="admin_view_area.php" class="btn cancel-btn" style="text-decoration: none; padding: 10px 20px; display: inline-block;">Cancel</a>
            </div>
        </form>
    </div>
</div>


</body>
</html>
