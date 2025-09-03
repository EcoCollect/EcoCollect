<?php
<<<<<<< HEAD
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

=======
include('../db_connect/db_connect.php');

>>>>>>> 9cf3b64f7d69f7b3281d8dc73055b26a706c1b65
$message = "";

// Fetch all areas for dropdown
$area_result = mysqli_query($conn, "SELECT area_id, area_name FROM area");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $plain_password = $_POST["password"];
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $status = $_POST["status"];
    $area_id = $_POST["area_id"];

<<<<<<< HEAD
    // Check if email already exists
    $check_sql = "SELECT email FROM agent WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $message = "Error: An agent with this email already exists.";
    } else {
        $sql = "INSERT INTO agent (email, password, name, phone, address, status, area_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $email, $hashed_password, $name, $phone, $address, $status, $area_id);

        if ($stmt->execute()) {
            $message = "Agent added successfully.";
        } else {
            $message = "Error: " . $stmt->error;
        }
=======
    $sql = "INSERT INTO agent (email, password, name, phone, address, status, area_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $email, $hashed_password, $name, $phone, $address, $status, $area_id);

    if ($stmt->execute()) {
        $message = "Agent added successfully.";
    } else {
        $message = "Error: " . $stmt->error;
>>>>>>> 9cf3b64f7d69f7b3281d8dc73055b26a706c1b65
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<<<<<<< HEAD
    <title>Add Agent | EcoCollect Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_styles.css">
    <style>
        .form_container {
            max-width: 500px;
            margin: 80px auto 10px auto;
            padding: 10px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }

        .form_container h2 {
            text-align: center;
            margin-bottom: 5px;
            color: #388e3c;
            font-size: 24px;
        }

        .form_container form {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .form_container label {
            font-weight: bold;
            color: #333;
            margin-bottom: 0px;
            display: block;
            font-size: 14px;
        }

        .form_container input,
        .form_container select,
        .form_container textarea {
            width: 100%;
            padding: 3px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form_container input:focus,
        .form_container select:focus,
        .form_container textarea:focus {
            outline: none;
            border-color: #388e3c;
            box-shadow: 0 0 3px rgba(56, 142, 60, 0.3);
        }

        .form_container textarea {
            resize: vertical;
            min-height: 25px;
        }

        .form_container button {
            background: linear-gradient(135deg, #388e3c, #66bb6a);
            color: white;
            padding: 6px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1px;
        }

        .form_container button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(56, 142, 60, 0.3);
        }

        .message {
            padding: 6px;
            border-radius: 5px;
            margin-bottom: 8px;
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }

        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .dashboard_btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 6px 16px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 5px;
            text-align: center;
            transition: background 0.3s ease;
            font-size: 14px;
        }

        .dashboard_btn:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
    <div class="form_container">
        <h2>Add New Agent</h2>

        <?php if ($message != ""): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" required placeholder="Enter agent email">

            <label>Password:</label>
            <input type="password" name="password" required placeholder="Enter password">

            <label>Name:</label>
            <input type="text" name="name" required placeholder="Enter full name">

            <label>Phone:</label>
            <input type="text" name="phone" placeholder="Enter phone number">

            <label>Address:</label>
            <textarea name="address" placeholder="Enter address"></textarea>

            <label>Area:</label>
            <select name="area_id" required>
                <option value="">-- Select Area --</option>
                <?php while ($row = mysqli_fetch_assoc($area_result)) { ?>
                    <option value="<?php echo $row['area_id']; ?>"><?php echo htmlspecialchars($row['area_name']); ?></option>
                <?php } ?>
            </select>

            <label>Status:</label>
            <select name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <button type="submit">➕ Add Agent</button>
=======
    <title>Add Agent</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Add New Agent</h2>
        <?php if ($message != "") echo "<p class='message'>$message</p>"; ?>
        <form method="POST" class="form-box">
            <label>Email:</label><br>
            <input type="email" name="email" required><br>

            <label>Password:</label><br>
            <input type="text" name="password" required><br>

            <label>Name:</label><br>
            <input type="text" name="name" required><br>

            <label>Phone:</label><br>
            <input type="text" name="phone"><br>

            <label>Address:</label><br>
            <textarea name="address"></textarea><br>

            <label>Area:</label><br>
            <select name="area_id" required>
                <option value="">-- Select Area --</option>
                <?php while ($row = mysqli_fetch_assoc($area_result)) { ?>
                    <option value="<?php echo $row['area_id']; ?>"><?php echo $row['area_name']; ?></option>
                <?php } ?>
            </select><br>

            <label>Status:</label><br>
            <select name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select><br>

            
            <button type="submit">Add Agent</button>
>>>>>>> 9cf3b64f7d69f7b3281d8dc73055b26a706c1b65
        </form>
    </div>
</body>
</html>
