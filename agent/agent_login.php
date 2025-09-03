<?php
session_start();
include('../db_connect/db_connect.php');

<<<<<<< HEAD
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, name, password FROM agent WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($agent_id, $agent_name, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            session_regenerate_id(true);
            $_SESSION['agent_id'] = $agent_id;
            $_SESSION['agent_name'] = $agent_name;
            header("Location: agent_dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Agent account not found.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Agent Login</title>
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
        <h1>Agent Login</h1>

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
=======
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM agent WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $agent = $result->fetch_assoc();
        if (password_verify($password, $agent['password'])) {
            $_SESSION['agent_id'] = $agent['id'];
            $_SESSION['agent_name'] = $agent['name'];
            header("Location: agent_dashboard.php");
            exit();
        } else {
            $message = "Invalid Password.";
        }
    } else {
        $message = "No agent found with that email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agent Login</title>
    <link rel="stylesheet" href="../assets/css/agent_styles.css">

</head>
<body>
    <h2>Agent Login</h2>
    <?php if ($message != "") echo "<p style='color:red;'>$message</p>"; ?>
    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
>>>>>>> 9cf3b64f7d69f7b3281d8dc73055b26a706c1b65
</body>
</html>
