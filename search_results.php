<?php
session_start();
include 'config.php'; // Database connection file

// Get the search query
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

if ($searchQuery) {
    // Prepare the SQL query to search for threads by title
    $sql = "SELECT * FROM threads WHERE title LIKE ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    
    // Use wildcard (%) for partial matching
    $searchTerm = "%$searchQuery%";
    mysqli_stmt_bind_param($stmt, "s", $searchTerm);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false; // No search query
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Style the search form */
        .search-form {
            display: flex;
            justify-content: center; /* Centers the search bar horizontally */
            align-items: center;
            margin-top: 10px;
        }

        /* Style the search input field */
        .search-form input {
            padding: 8px;
            width: 300px; /* Adjust width as needed */
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Style the search button */
        .search-form button {
            padding: 8px 15px;
            margin-left: 5px;
            background-color: #2E2EF2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Make the top section sticky */
        .home-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        /* Make the header (logo, welcome message, buttons) sticky */
        .header-container {
            position: sticky;
            top: 0;
            background-color: #1E1E1E; /* Match page background */
            width: 100%;
            padding: 10px 0;
            text-align: center;
            z-index: 1000; /* Ensure it stays above other content */
        }

        /* Ensure thread list is scrollable while keeping the header fixed */
        .thread-list {
            flex-grow: 1; /* Allow it to expand within available space */
            min-height: 0; /* Helps with flexbox scrolling */
            max-height: calc(100vh - 425px); /* Adjust to ensure full visibility */
            overflow-y: auto; /* Enable scrolling */
            padding-bottom: 100px; /* Prevent last thread from getting cut off */
        }

        .thread {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .thread h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .thread p {
            font-size: 1em;
            margin-bottom: 10px;
        }

        .thread p small {
            color: gray;
        }

        .thread-actions {
            margin-top: 10px;
        }

        .thread-actions button {
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .thread-actions button.dislike {
            background-color: #f44336;
        }

        .thread-likes-dislikes {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="home-container">
        <div class="header-container">
            <a href="home.php">← Back to Home</a>
            <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>

            <!-- Search form -->
            <div class="search-form">
                <form action="search_results.php" method="GET" class="search-form">
                    <input type="text" name="query" placeholder="Search threads..." value="<?php echo htmlspecialchars($searchQuery); ?>" required>
                    <button type="submit">Search</button>
                </form>
            </div>
        </div>

        <div class="thread-list">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="thread">
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
            <?php else: ?>
                <p>No threads found matching your search.</p>
            <?php endif; ?>
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
