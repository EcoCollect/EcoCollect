<?php
include('../db_connect/db_connect.php');

$name = "Super Admin";
$email = "admin@ecocollect.com";
$password_plain = "admin123";
$hashed_password = password_hash($password_plain, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admin (admin_name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    echo "✅ Admin user inserted successfully.";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
