<?php
require '../db.php';
session_start();

$response = ['success' => false, 'like_count' => 0];

$post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : null;
$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

if ($post_id && $user_id) {
    // Check if the user has already liked this post
    $check_like_query = "SELECT id FROM post_likes WHERE user_id = ? AND post_id = ?";
    $stmt = mysqli_prepare($connection, $check_like_query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $like = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($like) {
        // Remove the like
        $delete_like_query = "DELETE FROM post_likes WHERE user_id = ? AND post_id = ?";
        $stmt = mysqli_prepare($connection, $delete_like_query);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $post_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Decrease the post's like count
        $update_like_count_query = "UPDATE posts SET likes = likes - 1 WHERE id = ?";
    } else {
        // Add the like
        $insert_like_query = "INSERT INTO post_likes (user_id, post_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($connection, $insert_like_query);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $post_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Increase the post's like count
        $update_like_count_query = "UPDATE posts SET likes = likes + 1 WHERE id = ?";
    }

    // Update the like count
    $stmt = mysqli_prepare($connection, $update_like_count_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Get the updated like count
    $get_like_count_query = "SELECT likes FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($connection, $get_like_count_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($post) {
        $response['success'] = true;
        $response['like_count'] = $post['likes'];
    }
}

mysqli_close($connection);

header('Content-Type: application/json');
echo json_encode($response);