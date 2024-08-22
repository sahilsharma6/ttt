<?php
session_start();
require '../db.php';

// header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
    exit;
}

// Check if necessary data is provided
if (!isset($_POST['comment_id'], $_POST['comment_text'])) {
    echo json_encode(['success' => false, 'error' => 'Missing data.']);
    exit;
}

$comment_id = (int) $_POST['comment_id'];
$comment_text = mysqli_real_escape_string($connection, $_POST['comment_text']);
$user_id = $_SESSION['user_id'];

// Update the comment in the database
$update_comment_query = "UPDATE comments SET comment = ? WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($connection, $update_comment_query);
mysqli_stmt_bind_param($stmt, "sii", $comment_text, $comment_id, $user_id);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update comment.']);
}

mysqli_stmt_close($stmt);
mysqli_close($connection);
