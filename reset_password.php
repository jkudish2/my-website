<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include 'config.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check if new password and confirm password match
    if ($new_password !== $confirm_password) {
        echo "Passwords do not match!";
        header("Location: reset_password.html", true, 301);
    } else {
        // Sanitize input to prevent SQL injection
        $username = mysqli_real_escape_string($conn, $username);
        $new_password = mysqli_real_escape_string($conn, $new_password);
        
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password in the database
        $sql = "UPDATE users SET password='$hashed_password' WHERE username='$username'";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: login.html", true, 301);
        } else {
            echo "Error updating password: " . $conn->error;
        }
    }

    // Close the database connection
    $conn->close();
}
?>


