<div class="navbar bg-base-100">
    <div class="flex-1">
        <a href="index.php" class="btn btn-ghost normal-case text-xl">NETH<span class="text-primary">FLIX</span></a>
    </div>
    <div class="flex-none">
        <?php if (isset($_SESSION["user_id"])) { ?>
            <ul class="menu menu-horizontal px-1">
                <li><a href="watch-list.php">Watch List</a></li>
                <li><a href="my-videos.php">My Videos</a></li>
                <li><a href="watch-list.php">Genre</a></li>
                <li><a href="add-video.php">Add Video</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        <?php }else { ?>
            <ul class="menu menu-horizontal px-1">
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        <?php } ?>
    </div>
</div>