<?php
include 'header.php';
$error = "";

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirect to the login page
    exit();
}

// Handle genre creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_genre"])) {
    try {
        // Get the form data
        $genre_name = $_POST["genre_name"];

        // Insert genre into the database
        $insertQuery = "INSERT INTO genres (name) VALUES ('$genre_name')";
        $conn = get_db_connection();
        if ($conn->query($insertQuery)) {
            echo "Genre created successfully!";
        } else {
            throw new Exception("Error creating genre: " . $conn->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle genre update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_genre"])) {
    try {
        // Get the form data
        $genre_id = $_POST["genre_id"];
        $genre_name = $_POST["genre_name"];

        // Update genre in the database
        $updateQuery = "UPDATE genres SET name = '$genre_name' WHERE genre_id = $genre_id";
        $conn = get_db_connection();
        if ($conn->query($updateQuery)) {
            echo "Genre updated successfully!";
        } else {
            throw new Exception("Error updating genre: " . $conn->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle genre deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_genre"])) {
    try {
        // Get the form data
        $genre_id = $_POST["genre_id"];

        // Delete genre from the database
        $conn = get_db_connection();
        $deleteQuery = "DELETE FROM video_genres WHERE genre_id = $genre_id;";
        $conn->query($deleteQuery);
        $deleteQuery = "DELETE FROM genres WHERE genre_id = $genre_id";
        if ($conn->query($deleteQuery)) {
            echo "Genre deleted successfully!";
        } else {
            throw new Exception("Error deleting genre: " . $conn->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Retrieve all genres from the database
$query = "SELECT * FROM genres";
$conn = get_db_connection();
$result = $conn->query($query);

?>

<main class="flex justify-center items-center">
    <section class="w-96 bg-base-100 pt-10">
        <h2 class="font-bold text-4xl mb-4">Genres</h2>
        <span class="text-error text-sm"><?= $error ?></span>

        <!-- Genre List -->
        <ul class="space-y-3">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <li class="flex gap-2 "> <span class="text-2xl"><?= $row["name"] ?></span>
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <input type="hidden" name="genre_id" value="<?= $row["genre_id"] ?>" >
                        <input type="hidden" name="genre_name" value="<?= $row["name"] ?>">
                        <button type="submit" name="edit_genre" class="btn btn-sm btn-active btn-warning">Edit</button>
                        <button type="submit" name="delete_genre" class="btn btn-sm btn-active btn-error">Delete</button>
                    </form>
                </li>
            <?php } ?>
        </ul>

        <!-- Genre Form - Add/Edit -->
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mt-4 flex items-center gap-2">
            <?php if (isset($_POST["edit_genre"])) { ?>
                    <input type="hidden" name="genre_id" value="<?= $_POST["genre_id"] ?>">
                    <input type="text" name="genre_name" value="<?= $_POST["genre_name"] ?>" class="input input-bordered">
                    <button type="submit" name="update_genre" class="btn btn-md btn-active btn-primary">Update Genre</button>
            <?php } else { ?>
                <input type="text" name="genre_name" placeholder="Enter genre name" class="input input-bordered">
                <button type="submit" name="create_genre" class="btn btn-md btn-active btn-primary">Create Genre</button>
            <?php } ?>
        </form>
    </section>
</main>

<?php
$conn->close();
?>
