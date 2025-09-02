<?php
include('../db_connect/db_connect.php');
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $address = trim($_POST['address']);
    $area_id = intval($_POST['area_id']);

    if ($name && $email && $phone && $password && $address && $area_id) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO user (user_name, user_email, user_phone, user_password, user_address, area_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $email, $phone, $hashed_password, $address, $area_id);

        if ($stmt->execute()) {
            $message = "✅ Registration successful! You can now login.";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "❌ All fields are required.";
    }
}

$areas = $conn->query("SELECT area_id, area_name FROM area");
?>
<!DOCTYPE html>
<html>
<head>
    <title>EcoCollect | User Registration</title>
    <link rel="stylesheet" href="../assets/css/user_styles.css">
</head>
<body>
<div class="login_container">
    <div class="login_form_container">
        <div class="left">
            <form class="form_container" method="POST">
                <h1>Register</h1>
                <?php if ($message) echo "<div class='error_msg'>$message</div>"; ?>
                <input type="text" name="name" placeholder="Full Name" class="input" required>
                <input type="email" name="email" placeholder="Email" class="input" required>
                <input type="text" name="phone" placeholder="Phone" class="input" required>
                <input type="password" name="password" placeholder="Password" class="input" required>
                <input type="text" name="address" placeholder="Address" class="input" required>
                <select name="area_id" class="input" required>
                    <option value="">Select Area</option>
                    <?php while ($row = $areas->fetch_assoc()): ?>
                        <option value="<?php echo $row['area_id']; ?>"><?php echo $row['area_name']; ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="green_btn">Register</button>
            </form>
        </div>
        <div class="right">
            <h1>Welcome Back!</h1>
            <button class="white_btn" onclick="window.location.href='user_login.php'">Login</button>
            <button class="white_btn" onclick="window.location.href='../index.php'">Back to Home</button>
        </div>
    </div>
</div>
</body>
</html>