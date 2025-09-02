<?php
include('../db_connect/db_connect.php'); 

// Fetch areas for dropdown
$area_result = $conn->query("SELECT * FROM area");

// Fetch schedules for dropdown
$schedule_result = $conn->query("SELECT schedule_id, collection_date, collection_time FROM schedule");

// Fetch waste types for dropdown
$waste_result = $conn->query("SELECT * FROM waste_type");

$message = ""; // Store success/error message

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $area_id = $_POST['area_id'];
    $waste_type = $_POST['waste_type'];
    $weight = $_POST['weight'];
    $schedule_id = $_POST['schedule_id'];
    $remarks = $_POST['remarks'];

    $stmt = $conn->prepare("INSERT INTO request_collection (name, area_id, waste_type, weight, schedule_id, remarks) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisdis", $name, $area_id, $waste_type, $weight, $schedule_id, $remarks);

    if ($stmt->execute()) {
        $message = "<p class='success'>Request submitted successfully!</p>";
    } else {
        $message = "<p class='error'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
?>

<?php include 'user_navbar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>User Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 400px;
            margin: 50px auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #388e3c;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        label {
            display: block;
            margin-top: 12px;
            margin-bottom: 6px;
            font-weight: bold;
            color: #333;
        }
        input, select, textarea, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
        }
        button {
            background: #27ae60;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            border: none;
        }
        button:hover {
            background: #219150;
        }
    </style>
</head>
<body>
    <div class="page_content">
        <div class="container">
        <h2>Submit Waste Collection Request</h2>

        <!-- Show success/error message inside the form box -->
        <?php if (!empty($message)) echo $message; ?>

        <form method="POST">
            <label>Name:</label>
            <input type="text" name="name" required>

            <label>Area:</label>
            <select name="area_id" required>
                <option value="">--Select Area--</option>
                <?php while($row = $area_result->fetch_assoc()) { ?>
                    <option value="<?php echo $row['area_id']; ?>"><?php echo $row['area_name']; ?></option>
                <?php } ?>
            </select>

            <label>Waste Type:</label>
            <select name="waste_type" required>
                <option value="">--Select Waste Type--</option>
                <?php while($row = $waste_result->fetch_assoc()) { ?>
                    <option value="<?php echo $row['waste_name']; ?>"><?php echo $row['waste_name']; ?></option>
                <?php } ?>
            </select>

            <label>Weight (kg):</label>
            <input type="number" name="weight" step="0.01" required>

            <label>Schedule:</label>
            <select name="schedule_id" required>
                <option value="">--Select Schedule--</option>
                <?php while($row = $schedule_result->fetch_assoc()) { ?>
                    <option value="<?php echo $row['schedule_id']; ?>">
                        <?php echo $row['collection_date'] . " - " . $row['collection_time']; ?>
                    </option>
                <?php } ?>
            </select>

            <label>Remarks:</label>
            <textarea name="remarks"></textarea>

            <button type="submit">Submit Request</button>
        </form>
        </div>
    </div>
</body>
</html>
