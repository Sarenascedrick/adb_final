<?php
include 'header.php';

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirect to the login page
    exit();
}

// Check if the video ID and rating are provided
if (isset($_POST['video_id']) && isset($_POST['rating'])) {
    $video_id = $_POST['video_id'];
    $rating = $_POST['rating'];

    // Check if the user has already rated the video
    $user_id = $_SESSION["user_id"];
    $conn = get_db_connection();
    $checkRatingQuery = "SELECT rating_id FROM video_ratings WHERE user_id = $user_id AND video_id = $video_id";
    $checkRatingResult = $conn->query($checkRatingQuery);

    if ($checkRatingResult->num_rows > 0) {
        // User has already rated the video, update the rating
        $rating_id = $checkRatingResult->fetch_assoc()['rating_id'];
        $updateRatingQuery = "UPDATE video_ratings SET rating = $rating WHERE rating_id = $rating_id";

        if ($conn->query($updateRatingQuery) === TRUE) {
            echo "Rating updated successfully!";
        } else {
            echo "Error updating rating: " . $conn->error;
        }
    } else {
        // User has not rated the video yet, insert a new rating
        $insertRatingQuery = "INSERT INTO video_ratings (user_id, video_id, rating) VALUES ($user_id, $video_id, $rating)";

        if ($conn->query($insertRatingQuery) === TRUE) {
            echo "Rating added successfully!";
        } else {
            echo "Error adding rating: " . $conn->error;
        }
    }

    $conn->close();
} else {
    echo "Invalid request.";
}
?>
