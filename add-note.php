<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $note_text = trim($_POST['note_text']);

    if (!empty($note_text)) {
        $query = "INSERT INTO notes (user_id, note_text) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $note_text);
        $stmt->execute();
    }
}

header("Location: code.php");
exit();
?>
