<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
include 'config.php'; // Database connection file

// Fetch all languages
$languageResult = mysqli_query($conn, "SELECT id, name FROM languages ORDER BY name ASC");
$languages = mysqli_fetch_all($languageResult, MYSQLI_ASSOC);

// Default sorting order
$order = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';

// Get the search query
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

// Get selected language ID
$selectedLanguageId = isset($_GET['language_id']) ? (int)$_GET['language_id'] : 0;

if ($searchQuery) {
    // Use LOWER to make case-insensitive search
    $sql = "SELECT * FROM threads WHERE LOWER(title) LIKE LOWER(?)";
    if ($selectedLanguageId) {
        $sql .= " AND language_id = ?";
    }
    $sql .= " ORDER BY created_at $order";
    $stmt = mysqli_prepare($conn, $sql);

    // Add wildcards to the search term
    $searchTerm = "%$searchQuery%";
    if ($selectedLanguageId) {
        // Bind parameters: 's' for string, 'i' for integer
        mysqli_stmt_bind_param($stmt, "si", $searchTerm, $selectedLanguageId);
    } else {
        mysqli_stmt_bind_param($stmt, "s", $searchTerm);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
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

    <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>
    <!-- Search form -->
    <form action="search_results.php" method="GET" class="search-form">
        <div class="search-bar">
            <input type="text" name="query" placeholder="Search threads..." value="<?php echo htmlspecialchars($searchQuery); ?>" required>
            <button type="submit">Search</button>
        </div>
    </form>

    <!-- Filter outside form -->
    <div class="filter-form">
        <label for="language_id">Language:</label>
        <select name="language_id" id="language_id">
            <option value="0">-- All Languages --</option>
            <?php foreach ($languages as $language): ?>
                <option value="<?= $language['id'] ?>" <?= ($selectedLanguageId == $language['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($language['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Sort button -->
    <div class="sort-button">
        <form action="search_results.php" method="GET">
            <input type="hidden" name="query" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <input type="hidden" name="language_id" value="<?php echo $selectedLanguageId; ?>">
            <button type="submit" name="order" value="<?php echo $order == 'DESC' ? 'asc' : 'desc'; ?>">
                <?php echo $order == 'DESC' ? 'Show Least Recent First' : 'Show Most Recent First'; ?>
            </button>
        </form>
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
    // Language dropdown change handler
    document.getElementById('language_id').addEventListener('change', function () {
        const languageId = this.value;
        const urlParams = new URLSearchParams(window.location.search);

        if (!urlParams.has('query')) {
            urlParams.set('query', '');
        }

        urlParams.set('language_id', languageId);
        window.location.href = "search_results.php?" + urlParams.toString();
    });

    function updateVotes(threadId, action) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update_votes.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.send("thread_id=" + threadId + "&action=" + action);

        xhr.onload = function () {
            if (xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    document.getElementById('like-count-' + threadId).textContent = response.likes;
                    document.getElementById('dislike-count-' + threadId).textContent = response.dislikes;
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
