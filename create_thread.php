<?php
session_start();
include 'config.php'; // Database connection file

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    die("You must be logged in to create a thread.");
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['content']);

    // Validate input
    if (empty($title) || empty($description)) {
        die("Title and content cannot be empty.");
    }

    // Sanitize input
    $title = mysqli_real_escape_string($conn, $title);
    $description = mysqli_real_escape_string($conn, $description);

    // Insert thread into database
    $sql = "INSERT INTO threads (title, description) VALUES ('$title', '$description')";

    if (mysqli_query($conn, $sql)) {
        header("Location: home.php"); // Redirect back to home after successful submission
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
