<?php
session_start();
include('../db_connect/db_connect.php');

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load .env
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Use $_ENV instead of getenv()
$email_user = $_ENV['EMAIL_USER'] ?? '';
$email_pass = $_ENV['EMAIL_PASS'] ?? '';

if (empty($email_user) || empty($email_pass)) {
    die("SMTP credentials not set. Please configure EMAIL_USER and EMAIL_PASS in .env file.");
}

// Check agent login
if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

// Get agent's area
$agent_id = $_SESSION['agent_id'];
$area_sql = "SELECT area_id FROM agent WHERE id=?";
$stmt = $conn->prepare($area_sql);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$area_result = $stmt->get_result();
$area_row = $area_result->fetch_assoc();
$area_id = $area_row['area_id'] ?? 0;

// Fetch all users in agent's area
$user_sql = "SELECT user_id, user_name, user_email FROM user WHERE area_id=?";
$u_stmt = $conn->prepare($user_sql);
$u_stmt->bind_param("i", $area_id);
$u_stmt->execute();
$users = $u_stmt->get_result();

$success_messages = [];
$error_messages = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message'] ?? '';

    if (empty($message)) {
        $error_messages[] = "Message cannot be empty.";
    } else {
        while ($user = $users->fetch_assoc()) {
            $user_id = $user['user_id'];
            $user_name = $user['user_name'];
            $user_email = $user['user_email'];

            // Insert notification into DB
            $insert = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $insert->bind_param("is", $user_id, $message);
            $insert->execute();

            // Send email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $email_user;
                $mail->Password   = $email_pass;
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom($email_user, "EcoCollect Agent");
                $mail->addAddress($user_email, $user_name);

                $mail->isHTML(true);
                $mail->Subject = "Notification from EcoCollect Agent";
                $mail->Body = "<p>Hello {$user_name},</p><p>{$message}</p><p>Regards,<br>EcoCollect</p>";

                $mail->send();
                $success_messages[] = "Notification sent to {$user_name} ({$user_email}) successfully!";
            } catch (Exception $e) {
                $error_messages[] = "Failed to send to {$user_name} ({$user_email}): " . $mail->ErrorInfo;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Notification - Agent</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container {
            max-width: 600px; margin: 30px auto; background: #fff;
            padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        h2 { color: #4CAF50; }
        textarea, button {
            width: 100%; padding: 10px; margin-bottom: 15px;
            border: 1px solid #ccc; border-radius: 4px;
        }
        button {
            background: #4CAF50; color: white; border: none; cursor: pointer;
        }
        button:hover { background: #45a049; }
        .success { color: green; margin-bottom: 10px; }
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Send Notification to All Users in Your Area</h2>

    <?php
    foreach ($success_messages as $s) echo "<p class='success'>{$s}</p>";
    foreach ($error_messages as $e) echo "<p class='error'>{$e}</p>";
    ?>

    <form method="POST">
        <label for="message">Message</label>
        <textarea name="message" rows="4" required></textarea>
        <button type="submit">Send Notification</button>
    </form>
</div>
</body>
</html>
