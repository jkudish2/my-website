<?php
include 'config.php';

if (!isset($_GET['thread_id'])) {
    die("Thread ID not provided.");
}

$thread_id = (int) $_GET['thread_id']; // Cast to an integer to ensure it's a number.

// Fetch comments with avatar
$commentsQuery = "SELECT c.comment, c.created_at, u.username, u.avatar 
                  FROM comments c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.thread_id = ? 
                  ORDER BY c.created_at ASC";
$stmt = $conn->prepare($commentsQuery);
if ($stmt === false) {
    die("Error preparing the statement: " . $conn->error);
}

$stmt->bind_param("i", $thread_id);
$stmt->execute();

$commentsResult = $stmt->get_result();

// Check if any comments exist
if ($commentsResult->num_rows > 0) {
    while ($comment = $commentsResult->fetch_assoc()) {
        // Format the timestamp (Optional: you can use DateTime to format the date)
        $formatted_date = date("F j, Y, g:i a", strtotime($comment['created_at']));

        echo "<div class='comment'>
                <img src='" . htmlspecialchars($comment['avatar']) . "' alt='User Avatar' class='avatar'>
                <p>" . nl2br(htmlspecialchars($comment['comment'])) . "</p>
                <small>" . $formatted_date . "</small>
              </div>";
    }
} else {
    echo "<p>No comments yet.</p>";
}
?>


