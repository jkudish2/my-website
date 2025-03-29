<?php
include 'config.php';

if (!isset($_GET['thread_id'])) {
    die("Thread ID not provided.");
}

$thread_id = $_GET['thread_id'];

$commentsQuery = "SELECT c.comment, c.created_at, u.username 
                  FROM comments c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.thread_id = ? 
                  ORDER BY c.created_at ASC";
$stmt = $conn->prepare($commentsQuery);
$stmt->bind_param("i", $thread_id);
$stmt->execute();
$commentsResult = $stmt->get_result();

while ($comment = $commentsResult->fetch_assoc()) {
    echo "<div class='comment'><strong>" . htmlspecialchars($comment['username']) . ":</strong><p>" . nl2br(htmlspecialchars($comment['comment'])) . "</p><small>" . $comment['created_at'] . "</small></div>";
}
?>
