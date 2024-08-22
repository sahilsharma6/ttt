<?php
session_start();
include '../db.php';

// Check if id is provided and user is logged in
if (isset($_POST['id']) && isset($_SESSION['user_id'])) {
    $delete_comment_id = (int) $_POST['id'];
    $user_id = $_SESSION['user_id'];

    // Prepare and execute the delete query
    $delete_comment_query = "DELETE FROM comments WHERE id = ? AND user_id = ?";
    if ($stmt = mysqli_prepare($connection, $delete_comment_query)) {
        mysqli_stmt_bind_param($stmt, "ii", $delete_comment_id, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            echo "success";
        } else {
            echo "Error: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement.";
    }
} else {
    echo "Invalid request.";
}

// Close database connection
mysqli_close($connection);