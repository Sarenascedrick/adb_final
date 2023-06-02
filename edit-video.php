<?php
include 'header.php';
$error = "";

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
    $query = "SELECT * FROM videos WHERE video_id = $video_id";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Handle video update
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                // Get the form data
                $title = $_POST["title"];
                $release_date = $_POST["release_date"];
                $duration = $_POST["duration"];
                $synopsis = $_POST["synopsis"];

                // Update video in the database
                $updateQuery = "UPDATE videos SET title = '$title', release_date = '$release_date', duration = $duration, synopsis = '$synopsis' WHERE video_id = $video_id";

                if ($conn->query($updateQuery)) {
                    echo "Video updated successfully!";
                } else {
                    throw new Exception("Error updating video: " . $conn->error);
                }

                // Update video genres
                $genre_ids = $_POST["genres"];

                // First, delete all existing genre associations for the video
                $deleteGenresQuery = "DELETE FROM video_genres WHERE video_id = $video_id";
                $conn->query($deleteGenresQuery);

                // Then, insert the updated genre associations
                foreach ($genre_ids as $genre_id) {
                    $insertGenreQuery = "INSERT INTO video_genres (video_id, genre_id) VALUES ($video_id, $genre_id)";
                    $conn->query($insertGenreQuery);
                }

                // Update cast members
                if (isset($_POST['cast'])) {
                    $castData = $_POST['cast'];
                    $conn->query("DELETE FROM video_cast WHERE video_id = $video_id");
                    foreach ($castData as $cast) {
                        $person_name = $cast['person_name'];
                        $role = $cast['role'];
                        echo $person_name .' '. $role . '<br>';
                        $insertCastQuery = "INSERT INTO video_cast (video_id, person_name, role) VALUES ($video_id, '$person_name', '$role')";
                        $conn->query($insertCastQuery);
                    }
                }
                header("Location: video.php?id=$video_id"); // Redirect to the video page
                exit();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        ?>

        <main class="flex justify-center items-center">
            <section class="w-96 bg-base-100 pt-10">
                <h2 class="font-bold text-4xl mb-4">Edit Video</h2>
                <span class="text-error text-sm"><?= $error ?></span>
                <form method="post" action="edit-video.php?id=<?= $video_id ?>" class="px-4 py-2">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text">Title</span>
                        </label>
                        <input type="text" name="title" placeholder="Enter video title" class="input input-bordered w-full" value="<?= $row["title"] ?>" required />
                    </div>
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text">Release Date</span>
                        </label>
                        <input type="date" name="release_date" placeholder="Enter video release date" class="input input-bordered w-full" value="<?= $row["release_date"] ?>" required />
                    </div>
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text">Duration (in minutes)</span>
                        </label>
                        <input type="number" name="duration" placeholder="Enter video duration" class="input input-bordered w-full" value="<?= $row["duration"] ?>" required />
                    </div>
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text">Synopsis</span>
                        </label>
                        <textarea name="synopsis" placeholder="Enter video duration" class="textarea textarea-bordered w-full" cols="5" required><?= $row["synopsis"] ?></textarea>
                    </div>
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text">Genres</span>
                        </label>
                        <?php
                        // Retrieve all genres from the database
                        $genresQuery = "SELECT * FROM genres";
                        $genresResult = $conn->query($genresQuery);

                        if ($genresResult->num_rows > 0) {
                            while ($genreRow = $genresResult->fetch_assoc()) {
                                $genre_id = $genreRow["genre_id"];
                                $genre_name = $genreRow["name"];
                                $checked = "";
                                $genreCheckQuery = "SELECT * FROM video_genres WHERE video_id = $video_id AND genre_id = $genre_id";
                                $genreCheckResult = $conn->query($genreCheckQuery);

                                if ($genreCheckResult->num_rows > 0) {
                                    $checked = "checked";
                                }
                                ?>
                                <label class="cursor-pointer label">
                                    <input type="checkbox" name="genres[]" value="<?= $genre_id ?>" class="checkbox checkbox-primary" <?= $checked ?>>
                                    <span class="ml-2"><?= $genre_name ?></span>
                                </label>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text">Cast</span>
                        </label>
                        <div id="cast-container">
                            <?php
                            $castQuery = "SELECT * FROM video_cast WHERE video_id = $video_id";
                            $castResult = $conn->query($castQuery);

                            if ($castResult->num_rows > 0) {
                                while ($castRow = $castResult->fetch_assoc()) {
                                    $cast_id = $castRow["cast_id"];
                                    $person_name = $castRow["person_name"];
                                    $role = $castRow["role"];
                                    ?>
                                    <div class="cast-item">
                                        <input type="hidden" name="cast[<?= $cast_id ?>][cast_id]" value="<?= $cast_id ?>">
                                        <div class="flex items-center">
                                            <input type="text" name="cast[<?= $cast_id ?>][person_name]" placeholder="Enter person name" class="input input-bordered w-1/2 mr-2" value="<?= $person_name ?>" required />
                                            <input type="text" name="cast[<?= $cast_id ?>][role]" placeholder="Enter role" class="input input-bordered w-1/2" value="<?= $role ?>" required />
                                        </div>
                                        <button type="button" class="btn btn-error btn-sm mt-2" onclick="removeCastItem(this)">Remove</button>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addCastItem()">Add Cast</button>
                    </div>
                    <div class="mt-4">
                        <input type="submit" value="Update Video" class="btn btn-active btn-primary w-full">
                    </div>
                </form>
            </section>
        </main>

        <script>
            function addCastItem() {
                const castContainer = document.getElementById('cast-container');
                const castItem = document.createElement('div');
                castItem.className = 'cast-item';
                castItem.innerHTML = `
                    <input type="hidden" name="cast[new][cast_id]" value="">
                    <div class="flex items-center">
                        <input type="text" name="cast[new][person_name]" placeholder="Enter person name" class="input input-bordered w-1/2 mr-2" required>
                        <input type="text" name="cast[new][role]" placeholder="Enter role" class="input input-bordered w-1/2" required>
                    </div>
                    <button type="button" class="btn btn-error btn-sm mt-2" onclick="removeCastItem(this)">Remove</button>
                `;
                castContainer.appendChild(castItem);
            }

            function removeCastItem(button) {
                const castItem = button.parentNode;
                castItem.parentNode.removeChild(castItem);
            }
        </script>

    <?php
    } else {
        echo "Video not found.";
    }

    $conn->close();
} else {
    echo "Invalid video ID.";
}
?>
