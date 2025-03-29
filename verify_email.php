<?php
session_start(); // Start session
require 'config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Store email in session
        $_SESSION["reset_email"] = $email;

        // Redirect to reset password page
        header("Location: reset_password.html");
        exit();
    } else {
        header("Location: verify_email.html?error=No account found with that email");
        exit();
    }
}
?>
