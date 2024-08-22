<?php
session_start();
require '../db.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : null;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

if ($post_id) {
    // Fetch comments with pagination
    $comments_query = "SELECT comments.id, comments.comment, comments.created_at, testt.username, comments.user_id 
                       FROM comments 
                       JOIN testt ON comments.user_id = testt.id 
                       WHERE post_id = ? 
                       ORDER BY comments.created_at DESC 
                       LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($connection, $comments_query);
    mysqli_stmt_bind_param($stmt, "iii", $post_id, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $comments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Calculate total number of comments
    $total_comments_query = "SELECT COUNT(*) as total FROM comments WHERE post_id = ?";
    $stmt = mysqli_prepare($connection, $total_comments_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_comments = mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);

    $total_pages = ceil($total_comments / $limit);

    // Return JSON response
    echo json_encode([
        'comments' => $comments,
        'total_pages' => $total_pages,
        "total_comments" => $total_comments
    ]);
}

mysqli_close($connection);