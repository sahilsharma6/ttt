<?php
require '../db.php';

// Get the JSON data from the request body
// $data = json_decode(file_get_contents('php://input'), true);

$post_id = isset($data['id']) ? (int) $data['id'] : 0;
$status = isset($data['status']) ? $data['status'] : '';

if ($post_id) {
    // Update the status in the database
    $update_query = "UPDATE posts SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($connection, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $status, $post_id);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}

mysqli_close($connection);