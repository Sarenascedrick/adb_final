<?php
include 'header.php';

$error = "";
// Check if the user is already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $error;
    // Get the form data
    $email = $_POST["email"];
    $password = $_POST["password"];

    $conn = get_db_connection();

    $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Store user data in session
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["email"] = $user["email"];
        header("Location: index.php"); // Redirect to a protected page
        exit();
    } else {
        $error = "Invalid credentials. Login failed!";
    }

    $conn->close();
}
?>

<main class="flex justify-center items-center">
   <section class="w-96 bg-base-100 pt-28">
       <form method="post" action="login.php" class="px-4 py-2">
           <h2 class="font-bold text-4xl mb-4">Login</h2>
            <span class="text-error text-sm"><?= $error ?></span>
           <div class="form-control w-full">
               <label class="label">
                   <span class="label-text">Email</span>
               </label>
               <input type="email" name="email" placeholder="Email" class="input input-bordered w-f      ull" required />
           </div>

           <div class="form-control w-full">
               <label class="label">
                   <span class="label-text">Password</span>
               </label>
               <input type="password" name="password" placeholder="Your Password" class="input input-bordered w-full" required />
           </div>
           <div class="mt-4">
               <input type="submit" value="Login" class="btn btn-active btn-primary w-full" >
           </div>
       </form>
   </section>
</main>
