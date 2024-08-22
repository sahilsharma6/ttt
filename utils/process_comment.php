<?php
include '../db.php'; // Adjust the path to your database connection

session_start(); // Start the session to access session variables

error_reporting(E_ALL);
ini_set('display_errors', 1);
// Initialize response array
$response = [
    'success' => false,
    'message' => 'Something went wrong'
];

// Check if the request is a POST request and contains 'comment'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_POST['post_id'])) {
    $comment = trim($_POST['comment']);
    $post_id = intval($_POST['post_id']); // Ensure post_id is an integer

    // Check if the user is logged in and the user ID is set in the session
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Check if the comment is not empty
        if (!empty($comment)) {
            // Prepare the SQL statement to prevent SQL injection
            $insert_comment_query = "INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $connection->prepare($insert_comment_query);
            if ($stmt) {
                $stmt->bind_param("iis", $post_id, $user_id, $comment);

                // Execute the statement
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Comment added successfully';
                } else {
                    $response['message'] = 'Failed to add comment: ' . $stmt->error;
                }

                // Close the statement
                $stmt->close();
            } else {
                $response['message'] = 'Failed to prepare statement: ';
            }
        } else {
            $response['message'] = 'Comment cannot be empty';
        }
    } else {
        $response['message'] = 'User not logged in';
    }
} else {
    $response['message'] = 'Invalid request';
}

// Output the response as JSON
echo json_encode($response);

// Close the database connection
$connection->close();
