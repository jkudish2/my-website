<?php
session_start();
include 'config.php'; // Database connection file

$isLoggedIn = isset($_SESSION['user_id']);
$id = $isLoggedIn ? $_SESSION['user_id'] : null;

// Fetch threads from database
$sql = "SELECT * FROM threads ORDER BY created_at DESC limit 5";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="home-container">
        <div class="header-container">
            <img src="logo-transparent-png - Copy.png" alt="Logo" class="logo">
            <?php if ($isLoggedIn): ?>
                <h2>Welcome to Ghost Talk!</h2>
                <a href="logout.php">Logout</a>
                <div class = 'search-form'>
                    <form action="search_results.php" method="GET" class="search-form">
                        <input type="text" name="query" placeholder="Search threads..." required>
                        <button type="submit">Search</button>
                    </form>
                </div>
                <a href="create_thread.html">
                    <button>Create Thread</button>
                </a>
            <?php else: ?>
                <h2>Welcome to Ghost Talk!</h2>
                <a href="login.html">Login</a> | <a href="register.html">Register</a>
            <?php endif; ?>

            <h2>Recent Threads</h2>
        </div>

        <div class="thread-list">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="thread" id="thread-<?php echo $row['id']; ?>">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                    <p><small>Posted on: <?php echo $row['created_at']; ?></small></p>
                    <div class="thread-likes-dislikes" id="likes-dislikes-<?php echo $row['id']; ?>">
                        <p>👍 <span id="like-count-<?php echo $row['id']; ?>"><?php echo $row['likes']; ?></span> | 👎 <span id="dislike-count-<?php echo $row['id']; ?>"><?php echo $row['dislikes']; ?></span></p>
                    </div>
                    <div class="thread-actions">
                        <button onclick="updateVotes(<?php echo $row['id']; ?>, 'like')">Like</button>
                        <button onclick="updateVotes(<?php echo $row['id']; ?>, 'dislike')" class="dislike">Dislike</button>
                    </div>
                    <div class="thread-actions">
                        <a href="thread.php?id=<?php echo $row['id']; ?>">
                            <button class="join-button">Join Thread</button>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        function updateVotes(threadId, action) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_votes.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            // Send thread ID and action (like/dislike) as POST parameters
            xhr.send("thread_id=" + threadId + "&action=" + action);

            // On success, update the like/dislike count
            xhr.onload = function() {
                if (xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        var likeCountElement = document.getElementById('like-count-' + threadId);
                        var dislikeCountElement = document.getElementById('dislike-count-' + threadId);
                        likeCountElement.textContent = response.likes;
                        dislikeCountElement.textContent = response.dislikes;
                    } else {
                        alert('Failed to update votes');
                    }
                }
            };
        }
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
