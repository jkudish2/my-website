<?php
session_start();
include 'config.php'; // Database connection

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to create a thread.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['content']);
    $language_id = isset($_POST['language_id']) ? (int) $_POST['language_id'] : null;

    if (empty($title) || empty($description) || empty($language_id)) {
        die("Title, content, and language are required.");
    }

    // Sanitize
    $title = mysqli_real_escape_string($conn, $title);
    $description = mysqli_real_escape_string($conn, $description);

    // Insert
    $sql = "INSERT INTO threads (title, description, language_id) VALUES ('$title', '$description', $language_id)";

    if (mysqli_query($conn, $sql)) {
        header("Location: home.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
