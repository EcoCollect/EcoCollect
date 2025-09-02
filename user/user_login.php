<?php
session_start();
include('../db_connect/db_connect.php');
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT user_id, user_name, user_password FROM user WHERE user_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $user_name, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $user_name;
                header("Location: user_dashboard.php");
                exit();
            } else {
                $message = "❌ Incorrect password.";
            }
        } else {
            $message = "❌ User not found.";
        }
        $stmt->close();
    } else {
        $message = "❌ All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>EcoCollect | User Login</title>
    <link rel="stylesheet" href="../assets/css/user_styles.css">
</head>
<body>
<div class="login_container">
    <div class="login_form_container">
        <div class="left">
            <form class="form_container" method="POST">
                <h1>Login</h1>
                <?php if ($message) echo "<div class='error_msg'>$message</div>"; ?>
                <input type="email" name="email" placeholder="Email" class="input" required>
                <input type="password" name="password" placeholder="Password" class="input" required>
                <button type="submit" class="green_btn">Login</button>
            </form>
        </div>
        <div class="right">
            <h1>New Here?</h1>
            <button class="white_btn" onclick="window.location.href='user_registration.php'">Register</button>
            <button class="white_btn" onclick="window.location.href='../index.php'">Back to Home</button>
        </div>
    </div>
</div>
</body>
</html>