<?php
include('../db_connect/db_connect.php'); 

// Fetch areas for dropdown
$area_result = $conn->query("SELECT * FROM area");

// Fetch schedules for dropdown, excluding expired
$schedule_result = $conn->query("SELECT schedule_id, collection_date, collection_time, waste_type FROM schedule WHERE status != 'Expired'");

// Fetch waste types for dropdown, excluding 'all' and 'textile' if present
$waste_result = $conn->query("SELECT * FROM waste_type WHERE waste_name NOT IN ('all', 'textile')");

$message = ""; // Store success/error message

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $area_id = $_POST['area_id'];
    $waste_types = $_POST['waste_type'];
    $weights = $_POST['weight'];
    $schedule_id = $_POST['schedule_id'];
    $remarks = $_POST['remarks'];

    $success = true;
    $stmt = $conn->prepare("INSERT INTO request_collection (name, area_id, waste_type, weight, schedule_id, remarks) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisdis", $name, $area_id, $waste_type, $weight, $schedule_id, $remarks);

    for ($i = 0; $i < count($waste_types); $i++) {
        $waste_type = $waste_types[$i];
        $weight = $weights[$i];
        if (!$stmt->execute()) {
            $success = false;
            break;
        }
    }

    if ($success) {
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
                <?php while($row = $area_result->fetch_assoc()) { ?>
                    <option value="<?php echo $row['area_id']; ?>"><?php echo $row['area_name']; ?></option>
                <?php } ?>
            </select>

            <label>Schedule:</label>
            <select name="schedule_id" id="schedule_id" required>
                <?php while($row = $schedule_result->fetch_assoc()) { ?>
                    <option value="<?php echo $row['schedule_id']; ?>" data-waste-type="<?php echo htmlspecialchars($row['waste_type']); ?>">
                        <?php echo $row['collection_date'] . " - " . $row['collection_time']; ?>
                    </option>
                <?php } ?>
            </select>

            <div id="waste-items">
                <div class="waste-item">
                    <label>Waste Type:</label>
                    <select name="waste_type[]" required>
                        <option value="">Select Type</option>
                        <?php
                        $waste_result_copy = $conn->query("SELECT * FROM waste_type WHERE waste_name NOT IN ('all', 'textile')");
                        while($row = $waste_result_copy->fetch_assoc()) { ?>
                            <option value="<?php echo $row['waste_name']; ?>"><?php echo $row['waste_name']; ?></option>
                        <?php } ?>
                    </select>

                    <label>Weight (kg):</label>
                    <input type="number" name="weight[]" step="0.01" required>
                </div>
            </div>

            <button type="button" id="add-waste" style="background: none; color: #007bff; border: none; text-decoration: underline; cursor: pointer; padding: 0; margin-bottom: 12px;">Add Another Waste Type</button>

            <label>Remarks:</label>
            <textarea name="remarks"></textarea>

            <button type="submit">Submit Request</button>
        </form>
        </div>
    </div>

    <script>
        function filterWasteTypes() {
            const scheduleSelect = document.getElementById('schedule_id');
            const selectedOption = scheduleSelect.options[scheduleSelect.selectedIndex];
            const wasteType = selectedOption ? selectedOption.getAttribute('data-waste-type') : '';
            const allowedTypes = wasteType === 'all' ? [] : wasteType.split(',').map(t => t.trim());

            const wasteSelects = document.querySelectorAll('select[name="waste_type[]"]');
            wasteSelects.forEach(select => {
                const options = select.querySelectorAll('option');
                options.forEach(option => {
                    if (wasteType === 'all' || allowedTypes.includes(option.value)) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                        if (option.selected) {
                            option.selected = false;
                        }
                    }
                });
            });
        }

        document.getElementById('schedule_id').addEventListener('change', filterWasteTypes);

        document.getElementById('add-waste').addEventListener('click', function() {
            const wasteItems = document.getElementById('waste-items');
            const firstItem = wasteItems.querySelector('.waste-item');
            const newItem = firstItem.cloneNode(true);
            // Clear the values in the new item
            const selects = newItem.querySelectorAll('select');
            const inputs = newItem.querySelectorAll('input');
            selects.forEach(select => select.selectedIndex = 0);
            inputs.forEach(input => input.value = '');
            wasteItems.appendChild(newItem);
            filterWasteTypes(); // Apply filter to new select
        });

        // Initial filter
        filterWasteTypes();
    </script>
</body>
</html>
