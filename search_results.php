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
</head>
<body>
    <div class="home-container">
        <div class="header-container">
            <a href="home.php">← Back to Home</a>
            <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>
        </div>

        <div class="thread-list">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="thread">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                        <p><small>Posted on: <?php echo $row['created_at']; ?></small></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No threads found matching your search.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>
