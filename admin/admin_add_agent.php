<?php
include('../db_connect/db_connect.php');

$message = "";

// Fetch all areas for dropdown
$area_result = mysqli_query($conn, "SELECT area_id, area_name FROM area");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $plain_password = $_POST["password"];
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $status = $_POST["status"];
    $area_id = $_POST["area_id"];

    $sql = "INSERT INTO agent (email, password, name, phone, address, status, area_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $email, $hashed_password, $name, $phone, $address, $status, $area_id);

    if ($stmt->execute()) {
        $message = "Agent added successfully.";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Agent</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>
    <div class="container">
        <h2>Add New Agent</h2>
        <?php if ($message != "") echo "<p class='message'>$message</p>"; ?>
        <form method="POST" class="form-box">
            <label>Email:</label><br>
            <input type="email" name="email" required><br>

            <label>Password:</label><br>
            <input type="text" name="password" required><br>

            <label>Name:</label><br>
            <input type="text" name="name" required><br>

            <label>Phone:</label><br>
            <input type="text" name="phone"><br>

            <label>Address:</label><br>
            <textarea name="address"></textarea><br>

            <label>Area:</label><br>
            <select name="area_id" required>
                <option value="">-- Select Area --</option>
                <?php while ($row = mysqli_fetch_assoc($area_result)) { ?>
                    <option value="<?php echo $row['area_id']; ?>"><?php echo $row['area_name']; ?></option>
                <?php } ?>
            </select><br>

            <label>Status:</label><br>
            <select name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select><br>

            
            <button type="submit">Add Agent</button>
        </form>
    </div>
</body>
</html>
