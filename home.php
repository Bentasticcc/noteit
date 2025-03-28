<?php
  // You can include session management or any PHP logic here if needed
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home</title>
    <link rel="stylesheet" href="style.css" />
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

    <div class="content">
      <div class="image-container">
        <img src="images/img1.png" alt="NoteIt Illustration" />
      </div>
      <div class="text-container">
        <div class="info-box">
          <h2>Note<span>It!</span></h2>
          <p>
            Meet NoteIt!, the modernized app that makes note-taking a breeze.
            Jot down ideas effortlessly, organize them with ease, and retrieve
            information lightning-fast. Its customized formatting options and
            ideal sharing capabilities make NoteIt! an indispensable tool for
            maximizing your efficiency.
          </p>
          <a href="sign-in.php" class="sign-in-button">SIGN IN</a>
        </div>
      </div>
    </div>
  </body>
</html>
