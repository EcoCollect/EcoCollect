w<?php
session_start();
include('../db_connect/db_connect.php');

// Optional: protect the page if admin is not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_name = trim($_POST["admin_name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($admin_name) && !empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO admin (admin_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $admin_name, $email, $hashed_password);

        if ($stmt->execute()) {
            $message = "✅ New admin added successfully.";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "❌ All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Admin - EcoCollect</title>
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
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            color: #388e3c;
            margin-bottom: 25px;
            text-align: center;
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-container input:focus {
            outline: none;
            border-color: #388e3c;
        }
        .form-container button {
            background-color: #388e3c;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            transition: background-color 0.3s;
        }
        .form-container button:hover {
            background-color: #2e7d32;
        }
        .message {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            text-decoration: none;
        }
        .back-link:hover {
            color: #5a6268;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="content">
    <div class="form-container">
        <h2>Add New Admin</h2>
        <?php if ($message != ""): ?>
            <div class="<?php echo strpos($message, 'Error') !== false || strpos($message, 'required') !== false ? 'error' : 'message'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <label>Admin Name:</label>
            <input type="text" name="admin_name" placeholder="Enter admin name" required>

            <label>Email:</label>
            <input type="email" name="email" placeholder="Enter email address" required>

            <label>Password:</label>
            <input type="password" name="password" placeholder="Enter password" required>

            <button type="submit">Add Admin</button>
        </form>
        <a href="admin_dashboard.php" class="back-link">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>
