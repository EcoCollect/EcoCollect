<?php
session_start();
include('../db_connect/db_connect.php');
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
    $dotenv->load();
}

if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

// Fetch all users in agent's area
$agent_id = $_SESSION['agent_id'];
$area_query = $conn->prepare("SELECT area_id FROM agent WHERE id = ?");
$area_query->bind_param("i", $agent_id);
$area_query->execute();
$area_result = $area_query->get_result();
$area_id = $area_result->fetch_assoc()['area_id'];

$users_query = $conn->prepare("SELECT user_id, user_name, user_email FROM user WHERE area_id = ?");
$users_query->bind_param("i", $area_id);
$users_query->execute();
$users = $users_query->get_result();
$total_users = $users->num_rows;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message'];
    $success_count = 0;
    $error_messages = [];

    // Send to all users
    while ($user = $users->fetch_assoc()) {
        $user_id = $user['user_id'];
        $user_email = $user['user_email'];

        // Save to notifications table
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $message);
        $stmt->execute();

        // Send email using PHPMailer
        $email_user = getenv("EMAIL_USER");
        $email_pass = getenv("EMAIL_PASS");

        if (empty($email_user) || empty($email_pass)) {
            throw new Exception("SMTP credentials not set. Please configure EMAIL_USER and EMAIL_PASS in .env file.");
        }

        $mail = new PHPMailer(true);
        try {
            // Server settings for Gmail SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $email_user;
            $mail->Password   = $email_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('ecocollect1617@gmail.com', 'EcoCollect');
            $mail->addAddress($user_email);

            // Content
            $mail->isHTML(false);
            $mail->Subject = 'EcoCollect Notification';
            $mail->Body    = $message;

            $mail->send();
            $success_count++;
        } catch (Exception $e) {
            $error_messages[] = "Failed to send to $user_email: " . $mail->ErrorInfo;
            error_log("PHPMailer Error for $user_email: " . $mail->ErrorInfo);
        }
        $mail->clearAddresses();  // Clear for next user
    }

    if ($total_users == 0) {
        $success = "No users found in your area.";
    } else {
        $success = "Notification sent to $success_count out of $total_users users successfully!";
        if (!empty($error_messages)) {
            $success .= "<br>Errors:<br>" . implode("<br>", $error_messages);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Notifications - EcoCollect</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #e8f5e9, #f0fff0);
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 100px auto 30px;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #388e3c;
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 8px;
            font-weight: 500;
        }
        textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 16px;
        }
        button {
            background-color: #388e3c;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2e7d32;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }
        .info {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            color: #0d47a1;
        }
    </style>
</head>
<body>
<?php include('agent_navbar.php'); ?>
<div class="container">
    <h1>Send Notification to All Users</h1>

    <div class="info">
        This will send the notification to all users in your assigned area.
    </div>

    <?php if (!empty($success)): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="message">Message:</label>
        <textarea name="message" id="message" rows="6" required placeholder="Enter your notification message here..."></textarea>

        <button type="submit">Send Notification to All Users</button>
    </form>
</div>
</body>
</html>
