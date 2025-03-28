<?php
session_start();
include "db.php"; // Include database connection

// Fetch notes from the database
$sql = "SELECT title, content, date, dot FROM notes"; // Adjust table and column names if needed
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Note It!</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        .search-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 20px;
            background: #f8f8f8;
            padding: 10px 15px;
            border-radius: 20px;
            border: 1px solid #ccc;
        }

        .search-container input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 16px;
            background: transparent;
            opacity: 0.8;
            padding: 8px;
        }

        .search-container input::placeholder {
            opacity: 0.6;
        }

        .search-container .search-icon {
            color: gray;
            font-size: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <h1>Note<span>It!</span></h1>
            </div>
            <nav>
                <ul class="vertical-nav">
                    <li><a href="#"><i class="fas fa-sticky-note"></i> All Notes</a></li>
                    <li><a href="#"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li><a href="#"><i class="fas fa-box"></i> Archives</a></li>
                    <li><a href="sign-in.php"><i class="fas fa-power-off"></i> Logout</a></li>
                </ul>
            </nav>        
            <div class="user">
                <div class="status"></div>
                <p class="bold-text">Hi <?php echo $_SESSION["username"] ?? "Guest"; ?>!<br />Welcome back.</p>
            </div>
        </div>
        <div class="main-content">
            <header class="header">
                <h2>All Notes</h2>
                <div class="search">
                    <button class="add-note">
                        <img src="https://img.icons8.com/?size=100&id=1501&format=png&color=000000" alt="Add Notes" class="add-note-icon">
                        Add Notes
                    </button>
                </div>
            </header>

            <!-- ALIGNED SEARCH BAR ABOVE NOTES -->
            <div class="search-container">
                <input type="text" placeholder="Search notes...">
                <i class="fas fa-plus search-icon"></i>
            </div>

            <div class="notes-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($note = $result->fetch_assoc()): ?>
                        <div class="note-card">
                            <h3>
                                <?php echo htmlspecialchars($note["title"]); ?>
                                <span class="right-align">....</span>
                            </h3>
                            <p><?php echo nl2br(htmlspecialchars($note["content"])); ?></p>
                            <div class="note-footer">
                                <?php if (!empty($note["dot"])): ?>
                                    <span class="dot <?php echo htmlspecialchars($note["dot"]); ?>"></span>
                                <?php endif; ?>
                                <span class="note-date"><?php echo htmlspecialchars($note["date"]); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No notes found.</p>
                <?php endif; ?>
            </div>
        </div>        
</body>
</html>
