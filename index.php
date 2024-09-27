<?php
session_start();
require 'db.php';

// Fetch all categories
$all_categories_query = "SELECT id, category_name FROM categories";
$all_categories_result = mysqli_query($connection, $all_categories_query);
$categories = mysqli_fetch_all($all_categories_result, MYSQLI_ASSOC);

// Get category and post IDs from URL
$category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : null;
$edit_comment_id = isset($_GET['edit_comment_id']) ? (int) $_GET['edit_comment_id'] : null;
$delete_comment_id = isset($_GET['delete_comment_id']) ? (int) $_GET['delete_comment_id'] : null;

// Fetch posts for the selected category
$posts = [];
if ($category_id) {
    $posts_query = "SELECT id, title FROM posts WHERE category_id = ?";
    $stmt = mysqli_prepare($connection, $posts_query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Fetch the selected post
$current_post = [];
if ($post_id) {
    // Increment the view count
    $update_views_query = "UPDATE posts SET views = views + 1 WHERE id = ?";
    $stmt = mysqli_prepare($connection, $update_views_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Fetch the post data
    $post_query = "SELECT id, title, content, views FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($connection, $post_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $current_post = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Fetch comments for the selected post
    $comments_query = "SELECT comments.id, comments.comment, comments.created_at, testt.username, comments.user_id FROM comments JOIN testt ON comments.user_id = testt.id WHERE post_id = ? ORDER BY comments.created_at DESC";
    $stmt = mysqli_prepare($connection, $comments_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $comments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $comment = $_POST['comment'];

        if ($edit_comment_id) {
            // Update comment
            $update_comment_query = "UPDATE comments SET comment = ? WHERE id = ? AND user_id = ?";
            $stmt = mysqli_prepare($connection, $update_comment_query);
            mysqli_stmt_bind_param($stmt, "sii", $comment, $edit_comment_id, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            // Insert new comment
            $insert_comment_query = "INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($connection, $insert_comment_query);
            mysqli_stmt_bind_param($stmt, "iis", $post_id, $user_id, $comment);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        header("Location: ?category_id=$category_id&post_id=$post_id");
        exit;
    } else {
        header("Location: login.php");
        exit;
    }
}

// Handle comment deletion
if ($delete_comment_id && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $delete_comment_query = "DELETE FROM comments WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connection, $delete_comment_query);
    mysqli_stmt_bind_param($stmt, "ii", $delete_comment_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: ?category_id=$category_id&post_id=$post_id");
    exit;
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $current_post ? htmlspecialchars($current_post['title']) : 'My Website'; ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background-color: #f4f4f4;
        }

        .navbar {
            margin-bottom: 20px;
        }

        .sidebar {
            background-color: #fff;
            /* padding: 20px; */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .content {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .comment-form {
            margin-top: 30px;
        }

        .cd {
            background: #534b4b;
            display: inline-block;
            color: white;
            padding: 12px;
            width: 70%;
            /* margin: 42px; */
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="./"><img src="uploads/logo.png" height="80px" alt=""></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <?php foreach (array_reverse($categories) as $category): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?category_id=<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?></a>
                            </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <nav class="col-md-3 sidebar">
                <ul class="nav flex-column">
                    <?php foreach ($posts as $post): ?>
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="?category_id=<?php echo $category_id; ?>&post_id=<?php echo $post['id']; ?>"> <i
                                        class="fa-solid fa-check mx-2"></i><?php echo htmlspecialchars($post['title']); ?></a>
                            </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <main role="main" class="col-md-9 content">
                <?php if ($current_post): ?>
                        <h2><?php echo htmlspecialchars($current_post['title']); ?></h2>
                        <p><?php echo ($current_post['content']); ?></p>
                        <p><strong>Views: </strong><?php echo $current_post['views']; ?></p>
                        <div class="pagination mt-5 d-flex justify-content-between">
                            <?php
                            $previous_post_id = null;
                            $next_post_id = null;
                            foreach ($posts as $key => $post) {
                                if ($post['id'] == $current_post['id']) {
                                    if (isset($posts[$key - 1])) {
                                        $previous_post_id = $posts[$key - 1]['id'];
                                    }
                                    if (isset($posts[$key + 1])) {
                                        $next_post_id = $posts[$key + 1]['id'];
                                    }
                                }
                            }
                            ?>
                            <?php if ($previous_post_id): ?>
                                    <a class="btn btn-primary"
                                        href="?category_id=<?php echo $category_id; ?>&post_id=<?php echo $previous_post_id; ?>">Previous
                                        Post</a>&nbsp;&nbsp;
                            <?php endif; ?>
                            <?php if ($next_post_id): ?>
                                    <a class="btn btn-primary mx-3"
                                        href="?category_id=<?php echo $category_id; ?>&post_id=<?php echo $next_post_id; ?>">Next
                                        Post</a>
                            <?php endif; ?>
                        </div>

                        <!-- Comments Section -->
                        <div class="comments">
                            <h3 class="my-5">Comments</h3>
                            <?php if (!empty($comments)): ?>
                                    <?php foreach ($comments as $comment): ?>
                                            <div class="comment">
                                                <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                                                    <?php echo htmlspecialchars($comment['comment']); ?></p>
                                                <small><?php echo htmlspecialchars($comment['created_at']); ?></small>
                                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                                        <a
                                                            href="?category_id=<?php echo $category_id; ?>&post_id=<?php echo $post_id; ?>&edit_comment_id=<?php echo $comment['id']; ?>">Edit</a>
                                                        <a href="?category_id=<?php echo $category_id; ?>&post_id=<?php echo $post_id; ?>&delete_comment_id=<?php echo $comment['id']; ?>"
                                                            onclick="return confirm('Are you sure you want to delete this comment?')">Delete</a>
                                                <?php endif; ?>
                                            </div>
                                            <hr>
                                    <?php endforeach; ?>
                            <?php else: ?>
                                    <p>No comments yet.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Comment Form -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                                <form method="POST" action="" class="comment-form">
                                    <div class="form-group">
                                        <label
                                            for="comment"><?php echo $edit_comment_id ? 'Edit your comment:' : 'Add a comment:'; ?></label>
                                        <textarea class="form-control" id="comment" name="comment" rows="3"
                                            required><?php echo $edit_comment_id ? htmlspecialchars($comments[array_search($edit_comment_id, array_column($comments, 'id'))]['comment']) : ''; ?></textarea>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary my-3"><?php echo $edit_comment_id ? 'Update' : 'Submit'; ?></button>
                                </form>
                        <?php else: ?>
                                <p><a href="login.php">Login</a> to add a comment.</p>
                        <?php endif; ?>
                <?php else: ?>
                        <p>Select a post to view its content.</p>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>