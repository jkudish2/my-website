<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the user inputs
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $row['password'])) {
            session_start(); // Start the session
            $_SESSION['username'] = $username; // Store the username in the session
            
            // Redirect to the home page
            header("Location: home.php");
            exit();
        } else {
            // Invalid password error
            echo "Invalid password.";
        }
    } else {
        // No user found error
        echo "No user found.";
    }

    // Close the prepared statement and database connection
    $stmt->close();
    $conn->close();
}
?>
