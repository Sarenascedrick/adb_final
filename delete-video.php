<?php
include 'header.php';

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirect to the login page
    exit();
}

try {
    // Check if the video ID is provided
    if (isset($_POST['video_id'])) {
        $video_id = $_POST['video_id'];
        // Retrieve the video file path from the database
        $conn = get_db_connection();

        $query = "UPDATE videos SET soft_delete = 1 WHERE video_id = $video_id";

        if ($conn->query($query) === TRUE) {
            echo "Video and file deleted successfully!";
        } else {
            throw new Exception("Error deleting video record: " . $conn->error);
        }
        $conn->close();
    } else {
        throw new Exception("Invalid video ID.");
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>