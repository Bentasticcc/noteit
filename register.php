<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "noteit");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Validate fields
    if (!empty($username) && !empty($email) && !empty($password)) {
        // Hash password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert data into the users table
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            // Redirect to sign-in page after successful registration
            header("Location: sign-in.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "All fields are required!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <h1>Note<span>It!</span></h1>
        <nav>
            <ul>
                <li><a href="home.php">HOME</a></li>
                <li><a href="register.php">REGISTER</a></li>
                <li><a href="sign-in.php">SIGN IN</a></li>
            </ul>
        </nav>
    </div>

    <div class="container">
        <div class="mid-con">
            <div class="mid-logo">
                <img src="images/robot.gif" alt="Robot GIF" class="logo-gif">
                <h1>Note <span>It!</span></h1>
            </div>

            <form method="POST" action="">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>

                <div class="checkbox-container">
                    <input type="checkbox" id="show-password">
                    <label for="show-password">See Password</label>
                </div>

                <button type="submit" class="sign-up-btn">SIGN UP</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById("show-password").addEventListener("change", function() {
            let passwordField = document.getElementById("password");
            passwordField.type = this.checked ? "text" : "password";
        });
    </script>
</body>
</html>
