<?php
session_start();
include 'config.php'; // Database connection

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to create a thread.");
}

// Fetch languages for dropdown
$languages = [];
$result = mysqli_query($conn, "SELECT id, name FROM languages ORDER BY name ASC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $languages[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Thread</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <img src="ghost_talk_words.png" alt="Logo" class="logo">
    <div class="container">
        <h2>Create a New Thread</h2>
        <form action="submit_thread.php" method="POST">
            <label for="title">Thread Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="content">Thread Content:</label>
            <textarea id="content" name="content" rows="5" required></textarea>

            <label for="language">Thread Language:</label>
            <select id="language" name="language_id" required>
                <option value="">-- Select Language --</option>
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= $lang['id'] ?>"><?= htmlspecialchars($lang['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Create Thread</button>
        </form>
        <br>
        <a href="home.php">Back to Home</a>
    </div>
</body>
</html>

