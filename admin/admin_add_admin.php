<?php
session_start();
include('../db_connect/db_connect.php');

// Optional: protect the page if admin is not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
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
    <link rel="stylesheet" href="../assets/css/admin_styles.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>
    <div class="container">
        <h2>Add New Admin</h2>
        <?php if ($message != ""): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="admin_name" placeholder="Admin Name" required class="input">
            <input type="email" name="email" placeholder="Email" required class="input">
            <input type="password" name="password" placeholder="Password" required class="input">
            <button type="submit" class="green_btn">Add Admin</button>
        </form>
        <!-- <a href="admin_dashboard.php" class="white_btn">⬅ Back to Dashboard</a> -->
    </div>
</body>
</html>
