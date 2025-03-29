<?php
session_start();
require 'config.php';

// Check if session email exists
if (!isset($_SESSION["reset_email"])) {
    echo "Unauthorized access.";
    exit();
}

$email = $_SESSION["reset_email"]; // Get email from session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

    // Update password in the database
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
    $stmt->bind_param("ss", $new_password, $email);
    $stmt->execute();

    // Clear session data
    unset($_SESSION["reset_email"]);

    header("Location: login.html");
}
?>

