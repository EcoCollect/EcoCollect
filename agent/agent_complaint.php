<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

if (isset($_POST['submit'])) {
    $agent_id = $_SESSION['agent_id'];
    $complaint = $_POST['complaint'];

    $stmt = $conn->prepare("INSERT INTO agent_complaints (user_id, complaint_text) VALUES (?, ?)");
    $stmt->bind_param("is", $agent_id, $complaint);
    $stmt->execute();
    $stmt->close();

    $success = "Complaint submitted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Complaints & Feedback</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f0f2f5;
        }
        .content {
            margin-top: 80px; /* space for navbar */
            padding: 20px;
        }
        .form-container {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: none;
        }
        button {
            padding: 10px 20px;
            background: #66bb6a;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #388e3c;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<?php include('agent_navbar.php'); ?> <!-- Include the navbar -->

<div class="content">
    <div class="form-container">
        <h2>Complaints & Feedback</h2>
        <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
        <form method="POST">
            <textarea name="complaint" placeholder="Write your complaint or feedback here..." required></textarea>
            <button type="submit" name="submit">Submit</button>
        </form>
    </div>
</div>

</body>
</html>
