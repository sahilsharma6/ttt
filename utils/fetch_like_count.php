<?php
require '../db.php';
session_start();


$response = ['success' => false, 'like_count' => 0];
$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;

if ($post_id) {

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

header('Content-Type: application/json');


echo json_encode($response);
