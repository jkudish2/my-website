<?php
session_start();
include 'config.php'; // Database connection

if (!isset($_GET['id'])) {
    die("Thread ID not provided.");
}

$thread_id = $_GET['id'];

// Fetch thread details
$threadQuery = "SELECT * FROM threads WHERE id = ?";
$stmt = $conn->prepare($threadQuery);
$stmt->bind_param("i", $thread_id);
$stmt->execute();
$threadResult = $stmt->get_result();
$thread = $threadResult->fetch_assoc();

if (!$thread) {
    die("Thread not found.");
}

// Fetch comments with avatars
$commentsQuery = "SELECT c.comment, c.created_at, u.avatar 
                  FROM comments c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.thread_id = ? 
                  ORDER BY c.created_at ASC";
$stmt = $conn->prepare($commentsQuery);
$stmt->bind_param("i", $thread_id);
$stmt->execute();
$commentsResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($thread['title']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <a href="home.php">
            <button class="home-button">Home</button>
        </a>
    </div>
    <h2><?php echo htmlspecialchars($thread['title']); ?></h2>
    <p><?php echo nl2br(htmlspecialchars($thread['description'])); ?></p>
    <p><small>Created on: <?php echo $thread['created_at']; ?></small></p>

    <h3>Comments</h3>
    <div id="comments-section">
    <?php while ($comment = $commentsResult->fetch_assoc()): ?>
        <div class="comment">
            <div class="comment-content">
                <img src="<?php echo htmlspecialchars($comment['avatar']); ?>" alt="User Avatar" class="avatar">
                <p class="comment-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
            </div>
            <small><?php echo $comment['created_at']; ?></small>
        </div>

    <?php endwhile; ?>
</div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form id="comment-form">
            <textarea id="comment-text" placeholder="Write a comment..." required></textarea>
            <button type="submit">Post Comment</button>
        </form>
    <?php else: ?>
        <p><a href="login.html">Log in</a> to comment.</p>
    <?php endif; ?>

    <script>
        document.getElementById("comment-form").addEventListener("submit", function(event) {
            event.preventDefault();

            var commentText = document.getElementById("comment-text").value;
            var threadId = <?php echo $thread_id; ?>;
            
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "post_comment.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById("comments-section").innerHTML += xhr.responseText;
                    document.getElementById("comment-text").value = ""; // Clear input
                }
            };
            
            xhr.send("thread_id=" + threadId + "&comment=" + encodeURIComponent(commentText));
        });

        setInterval(function () {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_comments.php?thread_id=<?php echo $thread_id; ?>", true);
            xhr.onload = function () {
                if (xhr.status == 200) {
                    var commentsSection = document.getElementById("comments-section");
                    var newComments = xhr.responseText;
                    
                    // Append new comments instead of replacing all
                    commentsSection.innerHTML = newComments;
                }
            };
            xhr.send();
        }, 3000);

    </script>
</body>
</html>

<?php $conn->close(); ?>

