<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'], $_POST['thread_id'])) {
    $user_id = $_SESSION['user_id'];
    $thread_id = $_POST['thread_id'];
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (thread_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $thread_id, $user_id, $comment);
        $stmt->execute();

        echo "<div class='comment'><strong>Your Comment:</strong><p>" . htmlspecialchars($comment) . "</p></div>";
    }
}
?>
