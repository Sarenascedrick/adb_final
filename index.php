<?php
include 'header.php';

// Retrieve all videos from the database
$conn = get_db_connection();
$query = "SELECT v.video_id, v.title, v.release_date, v.duration, v.synopsis, v.video_link, u.name AS added_by
          FROM videos v
          INNER JOIN users u ON v.added_by = u.user_id WHERE soft_delete = 0";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    ?>
    <div class="grid grid-cols-4 py-20 gap-5">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <a href="video.php?id=<?= $row["video_id"] ?>" class="group">
                <div class="card max-w-96 bg-base-100 shadow-xl overflow-hidden">
                    <div class="max-h-[180px] overflow-hidden">
                        <video src="<?= $row["video_link"] ?>" controls class="group-hover:scale-125 transition"></video>
                    </div>
                    <div class="card-body">
                        <h2 class="card-title flex justify-between text-primary">
                            <span>
                                <?= $row["title"] ?>
                            </span>
                            <span class="text-xs font-normal">
                                <?= $row["duration"] ?> min
                            </span>
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
                    </div>
                </div>
            </a>
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
