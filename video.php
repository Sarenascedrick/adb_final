<?php
include 'header.php';

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirect to the login page
    exit();
}

// Check if the video ID is provided in the URL
if (isset($_GET['id'])) {
    $video_id = $_GET['id'];

    // Retrieve the specific video from the database
    $conn = get_db_connection();
    $query = "";

    $user_id = isset($_SESSION["user_id"]);
    $query = "SELECT v.video_id, v.title, v.release_date, v.duration, v.synopsis, v.video_link, u.name AS added_by
              FROM videos v
              INNER JOIN users u ON v.added_by = u.user_id
              WHERE v.video_id = $video_id";
    // Query to check if the video is in the user's watchlist
    $watchlistQuery = "SELECT COUNT(*) AS in_watchlist
                       FROM user_watchlist
                       WHERE user_id = {$_SESSION['user_id']} AND video_id = $video_id";

    $result = $conn->query($query);
    $watchlistResult = $conn->query($watchlistQuery);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $in_watchlist = ($watchlistResult->fetch_assoc())['in_watchlist'] == 1 ? true : false;

        // Retrieve video genres
        $genresQuery = "SELECT g.name
                        FROM video_genres vg
                        INNER JOIN genres g ON vg.genre_id = g.genre_id
                        WHERE vg.video_id = $video_id";
        $genresResult = $conn->query($genresQuery);
        $genres = [];
        if ($genresResult->num_rows > 0) {
            while ($genreRow = $genresResult->fetch_assoc()) {
                $genres[] = $genreRow["name"];
            }
        }

        // Retrieve average rating
        $averageRatingQuery = "SELECT AVG(rating) AS average_rating FROM video_ratings WHERE video_id = $video_id";
        $averageRatingResult = $conn->query($averageRatingQuery);
        $averageRating = ($averageRatingResult->fetch_assoc())['average_rating'];

        // Check if the current user has rated the video
        $userRatingQuery = "SELECT rating FROM video_ratings WHERE video_id = $video_id AND user_id = {$_SESSION['user_id']}";
        $userRatingResult = $conn->query($userRatingQuery);
        $userRating = ($userRatingResult->num_rows > 0) ? ($userRatingResult->fetch_assoc())['rating'] : null;
        ?>

        <div class="py-10">
            <div class="card max-w-[1080px] bg-base-100 shadow-xl mx-auto">
                <video src="<?= $row["video_link"] ?>" controls></video>
                <div class="card-body">
                    <div class="flex justify-between">
                        <h2 class="card-title text-primary text-4xl"><?= $row["title"] ?></h2>
                        <form method="post" action="<?php echo $in_watchlist ? 'remove-to-watchlist.php' : 'add-to-watchlist.php'; ?>">
                            <input type="hidden" name="video_id" value="<?= $row["video_id"] ?>">
                            <button type="submit" class="btn btn-active btn-primary"> <?php echo $in_watchlist ? 'Remove from Watch List' : 'Add to Watch List'; ?></button>
                        </form>
                    </div>
                    <p class="text-sm">Added by: <?= $row["added_by"] ?></p>
                    <p class="text-sm">Release Date: <?= $row["release_date"] ?></p>
                    <p class="text-sm">Duration: <?= $row["duration"] ?> minutes</p>
                    <p><?= $row["synopsis"] ?></p>
                     <p class="text-sm">
                      Rating:          <?php if ($averageRating) { ?>
                                   <?= number_format($averageRating, 1) ?>/5
                                <?php } else { ?>
                               No ratings yet
                                <?php } ?>
                            </p>

                            <div class="">
                                <h3 class="font-medium text-lg mb-2">Cast</h3>
                                <?php
                                $castQuery = "SELECT person_name, role FROM video_cast WHERE video_id = $video_id";
                                $castResult = $conn->query($castQuery);
                                if ($castResult->num_rows > 0) {
                                    while ($castRow = $castResult->fetch_assoc()) {
                                        ?>
                                        <div class="flex justify-between">
                                            <p class="font-medium"><?= $castRow["person_name"] ?></p>
                                            <p><?= $castRow["role"] ?></p>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo "No cast members found for this video.";
                                }
                                ?>
                                </div>
                    <div class="card-actions justify-end">
                        <p class="text-sm"><?= $row["release_date"] ?></p>
                        <?php
                        foreach ($genres as $genre) {
                            ?>
                            <div class="badge badge-outline"><?= $genre ?></div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-4 max-w-[1080px] mx-auto">
            <h3 class="font-bold text-lg mb-2">Add Rating</h3>
            <form method="post" action="add-rating.php">
                <input type="hidden" name="video_id" value="<?= $video_id ?>">
                <div class="flex items-center">
                    <label class="mr-2">Rating:</label>
                    <input type="range" name="rating" min="1" max="5" step="1" class="range-input" value="<?= $userRating ?? 3 ?>">
                </div>
                <button type="submit" class="btn btn-active btn-primary mt-2">Submit Rating</button>
            </form>
        </div>

    <?php
    } else {
        echo "Video not found.";
    }

    $conn->close();
} else {
    echo "Invalid video ID.";
}
?>
