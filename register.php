<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // List of avatar filenames
    $avatars = ['avatar_1.png', 'avatar_2.png', 'avatar_3.png', 'avatar_4.png', 'avatar_5.png', 'avatar_6.png'];

    // Pick a random avatar
    $randomAvatar = $avatars[array_rand($avatars)];

    // SQL query to insert the user along with the avatar filename
    $sql = "INSERT INTO users (username, email, password, avatar) VALUES ('$username', '$email', '$password', '$randomAvatar')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login.html", true, 301);  // Redirect after successful registration
    } else {
        echo "Error: " . $conn->error;  // Display error message if something goes wrong
    }

    $conn->close();
}
?>
