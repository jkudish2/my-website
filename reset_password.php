<?php
require 'conf.php'; // Include your existing database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Ensure passwords match
    if ($new_password !== $confirm_password) {
        echo "Passwords do not match.";
        exit; // Stop the script from running further
    }

    // Hash the password for security
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the password in the database
    $sql = "UPDATE users SET password = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        // Handle SQL preparation error
        echo "Error preparing statement: " . $conn->error;
        exit;
    }

    $stmt->bind_param("ss", $hashed_password, $username);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        echo "Password reset successfully. <a href='login.php'>Login here</a>";
    } else {
        echo "Error updating password: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

