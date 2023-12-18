<?php
include 'header.php';

// Check if the user is already logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
// Retrieve all videos from the database
$conn = get_db_connection();
$query = "SELECT v.video_id, v.title, v.release_date, v.duration, v.synopsis, v.video_link, u.name AS added_by
          FROM videos v
          INNER JOIN users u ON v.added_by = u.user_id
          WHERE v.added_by = $user_id AND soft_delete = 0";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    ?>
    <div class="grid grid-cols-4 py-20 gap-5">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="relative">
                <a href="video.php?id=<?= $row["video_id"] ?>" class="group">
                    <div class="card max-w-96 bg-base-100 shadow-xl overflow-hidden">
                        <div class="max-h-[180px] relative overflow-hidden">
                            <div class="absolute left-0 right-0 top-0 bottom-0 bg-neutral bg-opacity-20 z-[99]"></div>
                            <video src="<?= $row["video_link"] ?>" controls class="group-hover:scale-125 transition"></video>
                        </div>
                        <div class="card-body">
                            <h2 class="card-title flex justify-between text-primary">
                                <span><?= $row["title"] ?></span>
                                <span class="text-xs font-normal"><?= $row["duration"] ?> min</span>
                            </h2>
                            <div class="card-actions justify-end">
                                <p class="text-sm"><?= $row["release_date"] ?></p>
                                <?php
                                $video_id = $row["video_id"];
                                $genresQuery = "SELECT g.name
                                                FROM video_genres vg
                                                INNER JOIN genres g ON vg.genre_id = g.genre_id
                                                WHERE vg.video_id =$video_id";
                                $genresResult = $conn->query($genresQuery);
                                if ($genresResult->num_rows > 0) {
                                    while ($genreRow = $genresResult->fetch_assoc()) {
                                        ?>
                                        <div class="badge badge-outline"><?= $genreRow["name"] ?></div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <div class="flex justify-end gap-2 mt-2">
                                <a href='edit-video.php?id=<?= $row["video_id"] ?>' class="btn btn-sm btn-active btn-warning">edit</a>
                                <form method="post" action="delete-video.php">

                                   <input <"input type= "hidden"  name= "video_id" value="<?= $row["video_id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-active btn-error">delete</button>
                                                </form>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php }
        ?>
    </div>
    <?php
} else {
    echo "No videos found.";
}
$conn->close();
?>

<script defer>
    document.addEventListener("DOMContentLoaded", () => {
        mediaPlayer = document.querySelectorAll('video');
        mediaPlayer.forEach(item => {
            item.controls = false
        })


    }, false);
</script>
