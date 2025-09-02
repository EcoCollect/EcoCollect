<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $area_name = $_POST["area_name"];
    $district = $_POST["district"];
    $state = $_POST["state"];
    $pin_code = $_POST["pin_code"];
    $area_description = $_POST["area_description"];

    $sql = "INSERT INTO area (area_name, district, state, pin_code, area_description) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $area_name, $district, $state, $pin_code, $area_description);

    if ($stmt->execute()) {
        $message = "Area added successfully.";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Add Area</title>
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
        .form-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            color: #388e3c;
            margin-bottom: 20px;
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
    <div class="form-container">
        <h2>Add New Area</h2>
        <?php if ($message != "") echo "<p class='message'>$message</p>"; ?>
        <form method="POST">
            <label>Area Name:</label>
            <input type="text" name="area_name" required>

            <label>District:</label>
            <input type="text" name="district">

            <label>State:</label>
            <input type="text" name="state">

            <label>Pin Code:</label>
            <input type="text" name="pin_code">

            <label>Area Description:</label>
            <textarea name="area_description" rows="4"></textarea>

            <button type="submit">Add Area</button>
        </form>
    </div>
</div>
</body>
</html>
