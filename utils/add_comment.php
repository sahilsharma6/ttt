<?php
// add_comment.php

require '../db.php';

$post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
$comment = isset($_POST['comment']) ? mysqli_real_escape_string($connection, $_POST['comment']) : '';

if ($post_id && $comment) {
    $user_id = 1; // Replace this with logic to get the logged-in user's ID
    $query = "INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "iis", $post_id, $user_id, $comment);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($connection)]);
    }

    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}

mysqli_close($connection);