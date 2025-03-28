<?php
session_start();
include "db.php"; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Fetch the user by username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if the password matches (if stored using password_hash())
        if (password_verify($password, $user["password"])) {
            $_SESSION["username"] = $username;
            header("Location: code.php");
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign-in</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
        .mid-logo {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mid-logo img {
            width: 50px;
            height: 50px;
            margin-right: -10px; /* Removes spacing */
        }
        .mid-logo h1 {
            margin: 0;
        }
    </style>
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

    <div class="mid-con">
        <div class="mid-logo">
            <img src="images/robot.gif" alt="Robot Animation"><h1>Note<span>It!</span></h1>
        </div>
        <form method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <label><input type="checkbox" name="remember"> Sign Me In</label>

            <div class="forgot-password">
                <a href="#">Forgot Password?</a>
            </div>

            <?php if (isset($error)) { echo "<p class='error-message'>$error</p>"; } ?>

            <button type="submit" class="signin">SIGN IN</button>
        </form>
    </div>
</body>
</html>
