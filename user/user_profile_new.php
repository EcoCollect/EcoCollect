<?php
session_start();
include('../db_connect/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$edit_mode = isset($_GET['edit']) && $_GET['edit'] == '1';

// Fetch current user data
$stmt = $conn->prepare("SELECT user_name, user_email, user_phone, user_address, area_id, user_password FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $address, $area_id, $hashed_password);
$stmt->fetch();
$stmt->close();

// Fetch area name for display
$area_stmt = $conn->prepare("SELECT area_name FROM area WHERE area_id = ?");
$area_stmt->bind_param("i", $area_id);
$area_stmt->execute();
$area_stmt->bind_result($area_name);
$area_stmt->fetch();
$area_stmt->close();

// Fetch areas for dropdown
$areas = $conn->query("SELECT area_id, area_name FROM area");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_phone = trim($_POST['phone']);
    $new_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $current_password = trim($_POST['current_password']);
    $new_address = trim($_POST['address']);
    $new_area_id = intval($_POST['area_id']);

    if ($new_phone && $new_address && $new_area_id) {
        $update_password = false;
        if ($new_password) {
            if (strlen($new_password) < 8) {
                $message = "❌ New password must be at least 8 characters long.";
            } elseif ($new_password !== $confirm_password) {
                $message = "❌ New password and confirmation do not match.";
            } elseif (!password_verify($current_password, $hashed_password)) {
                $message = "❌ Current password is incorrect.";
            } else {
                $update_password = true;
            }
        }

        if (!$message) {
            $update_fields = "user_phone = ?, user_address = ?, area_id = ?";
            $params = [$new_phone, $new_address, $new_area_id];
            $types = "ssi";

            if ($update_password) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_fields .= ", user_password = ?";
                $params[] = $hashed_new_password;
                $types .= "s";
            }

            $stmt = $conn->prepare("UPDATE user SET $update_fields WHERE user_id = ?");
            $params[] = $user_id;
            $types .= "i";
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $message = "✅ Profile updated successfully!";
                // Refresh data
                $phone = $new_phone;
                $address = $new_address;
                $area_id = $new_area_id;
                $area_name = $areas->fetch_assoc()['area_name'];
                $edit_mode = false;
            } else {
                $message = "❌ Error updating profile: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "❌ All fields except password are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>EcoCollect | User Profile</title>
    <link rel="stylesheet" href="../assets/css/user_styles.css">
    <style>
        .profile_container {
            max-width: 600px;
            margin: 100px auto 50px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .profile_container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #388e3c;
        }
        .profile_view {
            margin-bottom: 30px;
        }
        .profile_field {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .profile_field label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }
        .profile_field .value {
            color: #333;
            font-size: 16px;
        }
        .edit_btn {
            background: #388e3c;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            margin: 0 auto;
        }
        .edit_btn:hover {
            background: #2e7d32;
        }
        .profile_form input, .profile_form select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .profile_form button {
            background: #388e3c;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .profile_form button:hover {
            background: #2e7d32;
        }
        .cancel_btn {
            background: #6c757d;
            margin-top: 10px;
        }
        .cancel_btn:hover {
            background: #5a6268;
        }
        .message {
            margin: 15px 0;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<?php include('user_navbar.php'); ?>
<div class="profile_container">
    <h1><?php echo $edit_mode ? 'Edit Profile' : 'My Profile'; ?></h1>
    <?php if ($message) echo "<div class='message " . (strpos($message, '✅') !== false ? 'success' : 'error') . "'>$message</div>"; ?>

    <?php if (!$edit_mode): ?>
        <!-- View Mode -->
        <div class="profile_view">
            <div class="profile_field">
                <label>Full Name</label>
                <div class="value"><?php echo htmlspecialchars($name); ?></div>
            </div>

            <div class="profile_field">
                <label>Email</label>
                <div class="value"><?php echo htmlspecialchars($email); ?></div>
            </div>

            <div class="profile_field">
                <label>Phone</label>
                <div class="value"><?php echo htmlspecialchars($phone); ?></div>
            </div>

            <div class="profile_field">
                <label>Address</label>
                <div class="value"><?php echo htmlspecialchars($address); ?></div>
            </div>

            <div class="profile_field">
                <label>Area</label>
                <div class="value"><?php echo htmlspecialchars($area_name); ?></div>
            </div>

            <button class="edit_btn" onclick="window.location.href='user_profile.php?edit=1'">Edit Profile</button>
        </div>
    <?php else: ?>
        <!-- Edit Mode -->
        <form class="profile_form" method="POST">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" disabled>

            <label for="email">Email</label>
            <input type="email" id="email" value="<?php echo htmlspecialchars($email); ?>" disabled>

            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>

            <label for="current_password">Current Password (Required if changing password)</label>
            <input type="password" id="current_password" name="current_password">

            <label for="password">New Password (Leave blank to keep current)</label>
            <input type="password" id="password" name="password">

            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password">

            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required>

            <label for="area_id">Area</label>
            <select id="area_id" name="area_id" required>
                <option value="">Select Area</option>
                <?php while ($row = $areas->fetch_assoc()): ?>
                    <option value="<?php echo $row['area_id']; ?>" <?php echo ($row['area_id'] == $area_id) ? 'selected' : ''; ?>><?php echo $row['area_name']; ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Update Profile</button>
            <button type="button" class="cancel_btn" onclick="window.location.href='user_profile.php'">Cancel</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
