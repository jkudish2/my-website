<?php
session_start(); // Start the session

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <img src="logo-transparent-png - Copy.png" alt="Logo" class="logo">
    <div class="home-container">
        <?php if ($isLoggedIn): ?>
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>You are logged in.</p>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <h2>Welcome to the Home Page</h2>
            <p>If you're logged in, you'll see a personalized message here.</p>
            <a href="login.html">Login</a> | <a href="register.html">Register</a>
        <?php endif; ?>
    </div>
</body>
</html>

