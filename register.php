<?php
include 'header.php';
$error = "";
// Check if the user is already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: index.php"); // Redirect to a protected page
    exit();
}

try {
    // Handle registration form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        global $error;
        // Get the form data
        $name = $_POST["name"];
        $email = $_POST["email"];
        $password = $_POST["password"];

        // Check if email is unique
        $conn = get_db_connection();

        $query = "SELECT * FROM users WHERE email='$email'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            throw new Exception("Email already exists: " . "The Email you used is already registered, try a new one");
        }

        // Insert user into the database
        $query = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        if (!$conn->query($query) === TRUE) {
            throw new Exception("Error registering user: " . $conn->error);
        }

        // Get the newly registered user from the database
        $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
        $result = $conn->query($query);

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Store user data in session
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["email"] = $user["email"];
            $conn->close();
            header("Location: index.php"); // Redirect to a protected page
            exit();
        } else {
            throw new Exception("Invalid credentials: " . "Invalid credentials. Login failed!");
        }
        $conn->close();

    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<main class="flex justify-center items-center">
    <section class="w-96 bg-base-100 pt-28">
        <form method="post" action="register.php" class="px-4 py-2">
            <h2 class="font-bold text-4xl mb-4">Register</h2>

            <span class="text-error text-sm"><?= $error ?></span>
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">UserName</span>
                </label>
                <input type="text" name="name" placeholder="Enter your name" class="input input-bordered w-full" required />
            </div>

            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Email</span>
                </label>
                <input type="email" name="email" placeholder="Enter your email" class="input input-bordered w-full" required />
            </div>

            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Password</span>
                </label>
                <input type="password" name="password" placeholder="Enter your password" class="input input-bordered w-full" required />
            </div>
            <div class="mt-4">
                <input type="submit" value="Register" class="btn btn-active btn-primary w-full" >
            </div>
        </form>
    </section>
</main>
