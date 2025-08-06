<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EcoCollect | Home</title>
    <link rel="stylesheet" href="assets/css/home_styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">🌿 EcoCollect</div>
        <div class="nav-links">
            <button onclick="window.location.href='user/user_login.php'">User Login</button>
            <button onclick="window.location.href='admin/admin_login.php'">Admin Login</button>
            <button onclick="window.location.href='agent/agent_login.php'">Agent Login</button>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="hero">
        <div class="overlay">
            <div class="hero-text">
                <h1>Welcome to EcoCollect</h1>
                <p>Empowering communities for a cleaner, greener tomorrow</p>
                <button onclick="window.location.href='user/user_registration.php'">Get Started</button>
            </div>
        </div>
    </div>

    <!-- Cards Section -->
    <div class="cards-container">
        <div class="card">
            <h2>Eco-Friendly Collection</h2>
            <p>Request eco-friendly waste collection at your doorstep easily through EcoCollect.</p>
        </div>
        <div class="card">
            <h2>Track Requests</h2>
            <p>Track your collection requests and view your collection history seamlessly.</p>
        </div>
        <div class="card">
            <h2>Admin & Agent Tools</h2>
            <p>Admins and Agents can manage collection schedules and requests efficiently.</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        © 2025 EcoCollect. All rights reserved.
    </div>

</body>
</html>
