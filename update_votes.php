<?php
session_start();
include 'config.php'; // Database connection file

$response = ['success' => false, 'likes' => 0, 'dislikes' => 0]; // Default response

// Check if necessary data is provided
if (isset($_POST['thread_id'], $_POST['action'], $_SESSION['user_id'])) {
    $thread_id = $_POST['thread_id'];
    $action = $_POST['action'];
    $user_id = $_SESSION['user_id'];

    // Update the vote in the database
    if ($action === 'like' || $action === 'dislike') {
        // Check if user has already voted on this thread
        $check_vote_sql = "SELECT * FROM user_votes WHERE user_id = $user_id AND thread_id = $thread_id";
        $check_vote_result = mysqli_query($conn, $check_vote_sql);
        $existing_vote = mysqli_fetch_assoc($check_vote_result);

        if ($existing_vote) {
            // User already voted, update their vote
            $update_vote_sql = "UPDATE user_votes SET vote = '$action' WHERE user_id = $user_id AND thread_id = $thread_id";
            mysqli_query($conn, $update_vote_sql);
        } else {
            // Insert new vote
            $insert_vote_sql = "INSERT INTO user_votes (user_id, thread_id, vote) VALUES ($user_id, $thread_id, '$action')";
            mysqli_query($conn, $insert_vote_sql);
        }

        // Update like/dislike counts in the threads table
        $update_counts_sql = "";
        if ($action === 'like') {
            $update_counts_sql = "UPDATE threads SET likes = likes + 1 WHERE id = $thread_id";
        } elseif ($action === 'dislike') {
            $update_counts_sql = "UPDATE threads SET dislikes = dislikes + 1 WHERE id = $thread_id";
        }

        if ($update_counts_sql) {
            mysqli_query($conn, $update_counts_sql);
        }

        // Get updated like/dislike counts
        $get_counts_sql = "SELECT likes, dislikes FROM threads WHERE id = $thread_id";
        $get_counts_result = mysqli_query($conn, $get_counts_sql);
        $counts = mysqli_fetch_assoc($get_counts_result);

        // Prepare the response
        $response = [
            'success' => true,
            'likes' => $counts['likes'],
            'dislikes' => $counts['dislikes']
        ];
    }
}

// Return the response as JSON
echo json_encode($response);

// Close the database connection
mysqli_close($conn);
?>
