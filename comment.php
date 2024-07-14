<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to comment.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $post_id = (int) $_POST['post_id'];
    $category_id = (int) $_POST['category_id'];
    $content = mysqli_real_escape_string($connection, $_POST['content']);

    // Debugging: Output the values being inserted
    echo "User ID: $user_id<br>";
    echo "Post ID: $post_id<br>";
    echo "Category ID: $category_id<br>";
    echo "Content: $content<br>";

    $query = "INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "iis", $post_id, $user_id, $content);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: viewp.php?category_id=$category_id&post_id=$post_id");
        exit();
    } else {
        echo "Failed to add comment.";
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($connection);
?>