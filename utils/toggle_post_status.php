<?php
require '../db.php';

$post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : null;
$status = isset($_POST['status']) ? $_POST['status'] : '';
if ($post_id) {
    // Determine the new status based on the current status
    $new_status = ($status === 'pending') ? 'approved' : 'pending';


    // Update the status in the database
    $update_query = "UPDATE posts SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($connection, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $post_id);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo json_encode(['success' => true, 'new_status' => $new_status]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }

    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}

mysqli_close($connection);