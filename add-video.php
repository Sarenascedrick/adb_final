<?php
include 'header.php';
$error = "";

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirect to the login page
    exit();
}

// Handle video upload and database insertion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get the form data
        $title = $_POST["title"];
        $release_date = $_POST["release_date"];
        $duration = $_POST["duration"];
        $synopsis = $_POST["synopsis"];
        $added_by = $_SESSION["user_id"];
        $genres = $_POST["genres"];

        // Handle video upload
        $target_dir = "uploads/";
        $videoFileType = strtolower(pathinfo($_FILES["video_file"]["name"], PATHINFO_EXTENSION));

        // Generate a unique random name for the video file
        $randomName = uniqid() . '-' . rand(1000, 9999);
        $target_file = $target_dir . $randomName . '.' . $videoFileType;

        // Check if the file is a valid video
        $validVideoTypes = array("mp4", "avi", "mov", "mkv");
        if (!in_array($videoFileType, $validVideoTypes)) {
            throw new Exception("Invalid video file format. Only MP4, AVI, MOV, and MKV formats are allowed.");
        }

        // Check if the file was successfully uploaded
        if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_file)) {
            // Insert video into the database
            $conn = get_db_connection();

            $query = "INSERT INTO videos (title, release_date, duration, synopsis, video_link, added_by)
                      VALUES ('$title', '$release_date', $duration, '$synopsis', '$target_file', $added_by)";

            if ($conn->query($query) === TRUE) {
                // Get the last inserted video ID
                $video_id = $conn->insert_id;

                // Insert video genres into the database
                foreach ($genres as $genre_id) {
                    $genre_query = "INSERT INTO video_genres (video_id, genre_id) VALUES ($video_id, $genre_id)";
                    $conn->query($genre_query);
                }

                 // Insert cast members into the database
                                $person_names = $_POST["person_name"];
                                $roles = $_POST["role"];
                                for ($i = 0; $i < count($person_names); $i++) {
                                    $person_name = $person_names[$i];
                                    $role = $roles[$i];

                                    $castQuery = "INSERT INTO video_cast VALUES (NULL, $video_id, '$person_name', '$role')";
                                    $conn->query($castQuery);
                                }


                 header("Location: index.php");
            } else {
                throw new Exception("Error adding video: " . $conn->error);
            }

            $conn->close();
        } else {
            throw new Exception("Sorry, there was an error uploading your video file.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch all genres from the database
$conn = get_db_connection();
$genresQuery = "SELECT * FROM genres";
$genresResult = $conn->query($genresQuery);
$genres = array();

if ($genresResult->num_rows > 0) {
    while ($genreRow = $genresResult->fetch_assoc()) {
        $genres[] = $genreRow;
    }
}

$conn->close();
?>

<main class="flex justify-center items-center">
    <section class="w-full max-w-[1080px] bg-base-100 pt-10">
        <h2 class="font-bold text-4xl mb-4">Add Video</h2>
        <span class="text-error text-sm"><?= $error ?></span>
        <form method="post" action="add-video.php" enctype="multipart/form-data" class="px-4 py-2">
             <div class="grid grid-cols-2 gap-4">
            <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Title</span>
                            </label>
                            <input type="text" name="title" placeholder="Enter video title" class="input input-bordered w-full" required />
                        </div>
                       <div class="grid grid-cols-2 gap-4">
                        <div class="form-control w-full">
                                       <label class="label">
                                           <span class="label-text">Release Date</span>
                                       </label>
                                       <input type="date" name="release_date" placeholder="Enter video release date" class="input input-bordered w-full" required />
                                   </div>
                                   <div class="form-control w-full">
                                       <label class="label">
                                           <span class="label-text">Duration (in minutes)</span>
                                       </label>
                                       <input type="number" name="duration" placeholder="Enter video duration" class="input input-bordered w-full" required />
                                   </div>
                        </div>
            </div>
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Synopsis</span>
                </label>
                <textarea name="synopsis" placeholder="Enter video duration" class="textarea textarea-bordered w-full" cols="5" required></textarea>
            </div>
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Genres</span>
                </label>
                <?php foreach ($genres as $genre) { ?>
                    <label class="cursor-pointer label">
                        <input type="checkbox" name="genres[]" value="<?= $genre["genre_id"] ?>" class="checkbox checkbox-primary">
                        <span class="ml-2"><?= $genre["name"] ?></span>
                    </label>
                <?php } ?>
            </div>
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Video File</span>
                </label>
                <input type="file" name="video_file" placeholder="Enter video duration" class="input input-bordered w-full" required />
            </div>

            <div  class="mt-4">
                            <h3 class="font-bold text-lg mb-2">Cast</h3>
                            <div id="cast-container">
                                <div class="cast-item">
                                                                <div class="form-control">
                                                                    <label class="label">
                                                                        <span class="label-text">Person Name</span>
                                                                    </label>
                                                                    <input type="text" name="person_name[]" placeholder="Enter person name" class="input input-bordered" required />
                                                                </div>
                                                                <div class="form-control">
                                                                    <label class="label">
                                                                        <span class="label-text">Role</span>
                                                                    </label>
                                                                    <input type="text" name="role[]" placeholder="Enter role" class="input input-bordered" required />
                                                                </div>
                                                            </div>
                            </div>
                            <button type="button" onclick="addCastItem()" class="btn btn-primary mt-2">Add Cast Member</button>
                        </div>
            <div class="mt-4">
                <input type="submit" value="Add Video" class="btn btn-active btn-primary w-full">
            </div>
        </form>
    </section>
</main>


<script>
    function addCastItem() {
        const castContainer = document.getElementById("cast-container");
        const castItem = document.createElement("div");
        castItem.classList.add("cast-item");
        castItem.innerHTML = `
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Person Name</span>
                </label>
                <input type="text" name="person_name[]" placeholder="Enter person name" class="input input-bordered" required />
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Role</span>
                </label>
                <input type="text" name="role[]" placeholder="Enter role" class="input input-bordered" required />
            </div>
        `;
        castContainer.appendChild(castItem);
    }
</script>







