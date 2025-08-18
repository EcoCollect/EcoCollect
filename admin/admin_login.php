<?php
session_start();
include('../db_connect/db_connect.php');

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT admin_id, admin_name, password FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($admin_id, $admin_name, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['admin_name'] = $admin_name;
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Admin account not found.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Admin Login</title>
    <link rel="stylesheet" href="../assets/css/admin_styles.css">
    <style>
        .toggle-password {
            cursor: pointer;
            font-size: 14px;
            color: #2c7a52;
            margin-top: -10px;
        }
    </style>
</head>
<body>
    <div class="login_container">
        <h1>Admin Login</h1>

        <?php if ($error): ?>
            <div class="error_msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" class="input" required>
            <input type="password" name="password" id="password" placeholder="Password" class="input" required>
            <div class="toggle-password" onclick="togglePassword()">Show/Hide Password</div>
            <button type="submit" class="green_btn">Login</button>
        </form>

        <a href="../index.php" class="back_btn">⬅ Back to Home</a>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById("password");
            pwd.type = pwd.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
