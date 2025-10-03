<?php
session_start();
include('../db_connect/db_connect.php');

require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
    $dotenv->load();
}

if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get requests with collection date 2 days from now
    $query = $conn->prepare("
        SELECT r.request_id, r.name, u.user_id, u.user_email, s.collection_date, s.collection_time
        FROM request_collection r
        JOIN user u ON r.area_id = u.area_id
        JOIN schedule s ON r.schedule_id = s.schedule_id
        WHERE DATE(s.collection_date) = DATE(DATE_ADD(NOW(), INTERVAL 2 DAY))
    ");
    $query->execute();
    $result = $query->get_result();

    $reminder_count = 0;
    while ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        $email = $row['user_email'];
        $date = $row['collection_date'];
        $time = $row['collection_time'];
        $message = "Reminder: Your waste collection is scheduled on $date at $time.";

        // Insert notification
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $message);
        $stmt->execute();

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv("EMAIL_USER");
            $mail->Password = getenv("EMAIL_PASS");
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom(getenv("EMAIL_USER"), 'EcoCollect');
            $mail->addAddress($email);

            $mail->isHTML(false);
            $mail->Subject = 'EcoCollect Reminder';
            $mail->Body = $message;

            $mail->send();
        } catch (Exception $e) {
            error_log("Failed to send email to $email: " . $mail->ErrorInfo);
        }

        $reminder_count++;
    }

    $success = "Reminders sent to $reminder_count users successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auto Reminders - EcoCollect</title>
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
        .info {
            background-color: #fff3e0;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #e65100;
            border-left: 4px solid #ff9800;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 15px;
            background-color: #e8f5e8;
            padding: 10px;
            border-radius: 6px;
        }
        form {
            text-align: center;
        }
        button {
            background-color: #388e3c;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2e7d32;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        .stat-box {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
        }
    </style>
</head>
<body>
<?php include('agent_navbar.php'); ?>
<div class="container">
    <h1>Send Auto Reminders</h1>

    <div class="info">
        This will automatically send reminders to users who have collections scheduled for 2 days from now.
    </div>

    <?php if (!empty($success)): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="post">
        <button type="submit">Send Reminders</button>
    </form>

    <div class="stats">
        <div class="stat-box">
            <strong>Next Reminder Check:</strong><br>
            <?php echo date('Y-m-d', strtotime('+2 days')); ?>
        </div>
    </div>
</div>
</body>
</html>
