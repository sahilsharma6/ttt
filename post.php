<?php
session_start();
require 'db.php';

// Get category and post IDs from URL
$category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : null;
$edit_comment_id = isset($_GET['edit_comment_id']) ? (int) $_GET['edit_comment_id'] : null;
$delete_comment_id = isset($_GET['delete_comment_id']) ? (int) $_GET['delete_comment_id'] : null;

// Fetch categories from the database
$categories = [];
$category_query = "SELECT id, category_name FROM categories";
$category_result = mysqli_query($connection, $category_query);
if ($category_result) {
    $categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
}

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

    if (!$post_id && count($posts) > 0) {
        $post_id = $posts[0]['id'];
    }
}

// Fetch the selected post
$current_post = [];
if ($post_id) {
    $update_views_query = "UPDATE posts SET views = views + 1 WHERE id = ?";
    $stmt = mysqli_prepare($connection, $update_views_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $post_query = "SELECT id, title, content, views FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($connection, $post_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $current_post = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Set default values if no post is found
    if (!$current_post) {
        $current_post = ['title' => 'No post found', 'content' => '', 'views' => 0];
    }

    // Fetch comments
    $comments_query = "SELECT comments.id, comments.comment, comments.created_at, testt.username, comments.user_id 
                        FROM comments 
                        JOIN testt ON comments.user_id = testt.id 
                        WHERE post_id = ? 
                        ORDER BY comments.created_at DESC";
    $stmt = mysqli_prepare($connection, $comments_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $comments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Determine the position of the current post in the $posts array
$current_post_index = array_search($post_id, array_column($posts, 'id'));

// Determine previous and next post IDs if they exist
$prev_post_id = $current_post_index > 0 ? $posts[$current_post_index - 1]['id'] : null;
$next_post_id = $current_post_index !== false && $current_post_index < count($posts) - 1 ? $posts[$current_post_index + 1]['id'] : null;

// Handle comment form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $comment = $_POST['comment'];

        if ($edit_comment_id) {
            $update_comment_query = "UPDATE comments SET comment = ? WHERE id = ? AND user_id = ?";
            $stmt = mysqli_prepare($connection, $update_comment_query);
            mysqli_stmt_bind_param($stmt, "sii", $comment, $edit_comment_id, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $insert_comment_query = "INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($connection, $insert_comment_query);
            mysqli_stmt_bind_param($stmt, "iis", $post_id, $user_id, $comment);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        header("Location: post.php?category_id=$category_id&post_id=$post_id");
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

    header("Location: post.php?category_id=$category_id&post_id=$post_id");
    exit;
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($current_post['title'] ?? 'Post'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .navbar {
            background-color: #004d40;
        }

        .nav-link {
            color: white !important;
        }

        .sidebar {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .sidebar h3 {
            color: #d32f2f;
        }

        .main-content {
            padding: 20px;
        }

        a {
            text-decoration: none;
        }

        ul li a {
            color: black;
            display: inline-block;
            padding-top: 5px;
        }

        .prev-next-buttons {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }

        .btn-nav {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
        }

        .btn-nav:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>

    <?php include_once 'header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 sidebar">
                <h3><?php echo htmlspecialchars($current_post['title'] ?? 'Posts'); ?></h3>
                <ul class="nav flex-column">
                    <?php foreach ($posts as $post): ?>
                        <li class="nav-item">
                            <i class="fa-solid fa-check mx-2" style="color:blue"></i>
                            <a class=""
                                href="post.php?category_id=<?php echo $category_id; ?>&post_id=<?php echo $post['id']; ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <main class="col-md-9 col-lg-10 main-content">
                <?php if ($current_post): ?>
                    <h1><?php echo htmlspecialchars($current_post['title']); ?></h1>
                    <p><?php echo (($current_post['content'])); ?></p>
                    <p><small>Views: <?php echo $current_post['views']; ?></small></p>

                    <div class="prev-next-buttons">
                        <?php if ($prev_post_id): ?>
                            <a class="btn-nav"
                                href="post.php?category_id=<?php echo $category_id; ?>&post_id=<?php echo $prev_post_id; ?>">←
                                Previous</a>
                        <?php endif; ?>
                        <?php if ($next_post_id): ?>
                            <a class="btn-nav"
                                href="post.php?category_id=<?php echo $category_id; ?>&post_id=<?php echo $next_post_id; ?>">Next
                                →</a>
                        <?php endif; ?>
                    </div>

                    <section>
                        <h2>Comments</h2>
                        <?php foreach ($comments as $comment): ?>
                            <div class="mb-3">
                                <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                                <p><small><?php echo htmlspecialchars($comment['created_at']); ?></small></p>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                    <a
                                        href="post.php?category_id=<?php echo $category_id; ?>&post_id=<?php echo $post_id; ?>&edit_comment_id=<?php echo $comment['id']; ?>">Edit</a>
                                    <a href="post.php?category_id=<?php echo $category_id; ?>&post_id=<?php echo $post_id; ?>&delete_comment_id=<?php echo $comment['id']; ?>"
                                        onclick="return confirm('Are you sure you want to delete this comment?')">Delete</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="post">
                                <div class="mb-3">
                                    <textarea class="form-control" name="comment" rows="3"
                                        required><?php echo $edit_comment_id && isset($comment_index) && $comment_index !== false ? htmlspecialchars($comments[$comment_index]['comment']) : ''; ?></textarea>
                                </div>
                                <button type="submit"
                                    class="btn btn-primary my-3"><?php echo $edit_comment_id ? 'Update' : 'Submit'; ?></button>
                            </form>
                        <?php else: ?>
                            <p>Please <a href="login.php">log in</a> to add a comment.</p>
                        <?php endif; ?>
                    </section>
                <?php else: ?>
                    <p>No post found.</p>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <?php include_once 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>