<?php
session_start();
include('../db_connect/db_connect.php');

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
</body>
</html>
