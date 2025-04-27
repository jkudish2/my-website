<?php
session_start();
include 'config.php'; // Database connection file

$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// Fetch threads from the database
$sql = "SELECT t.*, 
        (SELECT vote FROM user_votes WHERE user_id = '$user_id' AND thread_id = t.id) AS user_vote 
        FROM threads t 
        ORDER BY t.created_at DESC LIMIT 5";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
<nav class="navbar">
        <div class="nav-left">
            <a href="home.php" class="home-button">
                <i class="fas fa-home"></i> Home
            </a>
        </div>
        <div class="nav-right">
            <?php if ($isLoggedIn): ?>
                <a href="logout.php" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="login.html" class="nav-link">Login</a>
                <a href="register.html" class="nav-link">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="home-container">
        <div class="header-container">
            <img src="ghost_talk_words.png" alt="Logo" class="logo">
            <?php if ($isLoggedIn): ?>
                <h2>Welcome to Ghost Talk!</h2>
                <div class="search-form-home">
                    <form action="search_results.php" method="GET" class="search-form-home">
                        <input type="text" name="query" placeholder="Search threads..." required>
                        <button type="submit">Search</button>
                    </form>
                </div>
                <a href="create_thread.php">
                    <button>Create Thread</button>
                </a>
            <?php else: ?>
                <h2>Welcome to Ghost Talk!</h2>
            <?php endif; ?>

            <h2>Recent Threads</h2>
            
        </div>

    </div>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php $userVote = $row['user_vote']; ?>
                <div class="thread" id="thread-<?php echo $row['id']; ?>">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                    <p><small>Posted on: <?php echo $row['created_at']; ?></small></p>
                    <div class="thread-likes-dislikes">
                        <p>👍 <span id="like-count-<?php echo $row['id']; ?>"><?php echo $row['likes']; ?></span> | 
                        👎 <span id="dislike-count-<?php echo $row['id']; ?>"><?php echo $row['dislikes']; ?></span></p>
                    </div>
                    <?php if ($isLoggedIn): ?>
                        <div class="thread-actions">
                            <button onclick="updateVotes(<?php echo $row['id']; ?>, 'like')">Like</button>
                            <button onclick="updateVotes(<?php echo $row['id']; ?>, 'dislike')" class="dislike">Dislike</button>
                        </div>
                    <?php endif; ?>
                    <div class="thread-actions">
                        <a href="thread.php?id=<?php echo $row['id']; ?>">
                            <button class="join-button">Join Thread</button>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>

    <script>
        function updateVotes(threadId, action) {
            fetch("update_votes.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `thread_id=${threadId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`like-count-${threadId}`).textContent = data.likes;
                    document.getElementById(`dislike-count-${threadId}`).textContent = data.dislikes;

                    document.getElementById(`like-btn-${threadId}`).classList.toggle("active", action === "like");
                    document.getElementById(`dislike-btn-${threadId}`).classList.toggle("active", action === "dislike");
                }
            });
        }
    </script>
    <!-- Update the URL dynamically to a clean path without file extension -->
    <script>
        function updateURL(path) {
            let cleanPath = path.replace(/\.php$|\.html$/, '');
            window.history.replaceState(null, null, cleanPath);
        }

        window.onload = function() {
            const currentPath = window.location.pathname;
            if (currentPath === '/home.php') {
                updateURL('/');
            }
        };
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
