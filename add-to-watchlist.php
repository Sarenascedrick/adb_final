<?php
include 'header.php';

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirect to the login page
    exit();
}

// Check if the video ID is provided
if (isset($_POST['video_id'])) {
    $video_id = $_POST['video_id'];
    $user_id = $_SESSION["user_id"];

    // Perform necessary database operations to add the video to the user's watch list
    $conn = get_db_connection();

    // Check if the row already exists in the user_watchlist table
    $checkQuery = "SELECT * FROM user_watchlist WHERE user_id = $user_id AND video_id = $video_id";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        header("Location: video.php?id=$video_id");
        exit();
    } else {
        // Insert the video into the user_watchlist table
        $insertQuery = "INSERT INTO user_watchlist (user_id, video_id, added_date) VALUES ($user_id, $video_id, now())";
        if ($conn->query($insertQuery) === TRUE) {
            echo "Video added to watchlist successfully!";
        } else {
            echo "Error adding video to watchlist: " . $conn->error;
        }
    }

    $conn->close();

    // Redirect back to the video page or any other desired page
    header("Location: video.php?id=$video_id");
    exit();
} else {
    echo "Invalid request.";
}
?>
``
