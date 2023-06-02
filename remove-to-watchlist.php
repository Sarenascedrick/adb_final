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

    // Perform necessary database operations to remove the video from the user's watchlist
    $conn = get_db_connection();

    // Check if the row exists in the user_watchlist table
    $checkQuery = "SELECT * FROM user_watchlist WHERE user_id = $user_id AND video_id = $video_id";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        // Remove the video from the user_watchlist table
        $removeQuery = "DELETE FROM user_watchlist WHERE user_id = $user_id AND video_id = $video_id";
        if ($conn->query($removeQuery) === TRUE) {
            echo "Video removed from watchlist successfully!";
        } else {
            echo "Error removing video from watchlist: " . $conn->error;
        }
    } else {
        echo "The video is not in your watchlist.";
    }

    $conn->close();

    // Redirect back to the video page or any other desired page
    header("Location: video.php?id=$video_id");
    exit();
} else {
    echo "Invalid request.";
}
?>
