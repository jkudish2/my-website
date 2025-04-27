<?php
session_start();
include 'config.php'; // Database connection file

$response = ['success' => false, 'likes' => 0, 'dislikes' => 0]; // Default response

if (isset($_POST['thread_id'], $_POST['action'], $_SESSION['user_id'])) {
    $thread_id = (int)$_POST['thread_id']; // Sanitize input
    $action = $_POST['action'];
    $user_id = (int)$_SESSION['user_id'];

    // Check if user already voted
    $check_vote_sql = "SELECT vote FROM user_votes WHERE user_id = $user_id AND thread_id = $thread_id";
    $check_vote_result = mysqli_query($conn, $check_vote_sql);
    $existing_vote = mysqli_fetch_assoc($check_vote_result);

    if ($existing_vote) {
        if ($existing_vote['vote'] === $action) {
            // If user clicks the same vote, remove it
            $delete_vote_sql = "DELETE FROM user_votes WHERE user_id = $user_id AND thread_id = $thread_id";
            mysqli_query($conn, $delete_vote_sql);

            // Adjust counts accordingly
            if ($action === 'like') {
                mysqli_query($conn, "UPDATE threads SET likes = likes - 1 WHERE id = $thread_id");
            } else {
                mysqli_query($conn, "UPDATE threads SET dislikes = dislikes - 1 WHERE id = $thread_id");
            }
        } else {
            // User switched vote (like -> dislike or vice versa)
            $update_vote_sql = "UPDATE user_votes SET vote = '$action' WHERE user_id = $user_id AND thread_id = $thread_id";
            mysqli_query($conn, $update_vote_sql);

            // Adjust counts accordingly
            if ($action === 'like') {
                mysqli_query($conn, "UPDATE threads SET likes = likes + 1, dislikes = dislikes - 1 WHERE id = $thread_id");
            } else {
                mysqli_query($conn, "UPDATE threads SET dislikes = dislikes + 1, likes = likes - 1 WHERE id = $thread_id");
            }
        }
    } else {
        // Insert new vote
        $insert_vote_sql = "INSERT INTO user_votes (user_id, thread_id, vote) VALUES ($user_id, $thread_id, '$action')";
        mysqli_query($conn, $insert_vote_sql);

        // Update like/dislike counts
        if ($action === 'like') {
            mysqli_query($conn, "UPDATE threads SET likes = likes + 1 WHERE id = $thread_id");
        } else {
            mysqli_query($conn, "UPDATE threads SET dislikes = dislikes + 1 WHERE id = $thread_id");
        }
    }

    // Fetch updated like/dislike counts
    $get_counts_sql = "SELECT likes, dislikes FROM threads WHERE id = $thread_id";
    $get_counts_result = mysqli_query($conn, $get_counts_sql);
    $counts = mysqli_fetch_assoc($get_counts_result);

    $response = [
        'success' => true,
        'likes' => $counts['likes'],
        'dislikes' => $counts['dislikes']
    ];
}

// Return JSON response
echo json_encode($response);
mysqli_close($conn);
?>
