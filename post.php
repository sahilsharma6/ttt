<?php
session_start();
require 'db.php';

// Get category ID, subcategory ID, and post ID from URL
$category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : null;
$subcategory_id = isset($_GET['subcategory_id']) ? (int) $_GET['subcategory_id'] : null;

// Fetch categories from the database
$categories = [];
$category_query = "SELECT id, category_name FROM categories";
$category_result = mysqli_query($connection, $category_query);
if ($category_result) {
    $categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
}

// Fetch subcategories and posts for the selected category
$subcategories = [];
if ($category_id) {
    // Fetch subcategories
    $subcategories_query = "SELECT id, subcategory_name FROM subcategories WHERE category_id = ?";
    $stmt = mysqli_prepare($connection, $subcategories_query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $subcategories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // If no subcategory is selected, select the first subcategory by default
    if (!$subcategory_id && !empty($subcategories)) {
        $subcategory_id = $subcategories[0]['id'];
    }
}

// Fetch posts for the selected subcategory
$posts = [];
if ($subcategory_id) {
    $posts_query = "SELECT id, title FROM posts WHERE subcategory_id = ?";
    $stmt = mysqli_prepare($connection, $posts_query);
    mysqli_stmt_bind_param($stmt, "i", $subcategory_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    if (!$post_id && count($posts) > 0) {
        $post_id = $posts[0]['id']; // Default to the first post if none is selected
        header("Location: post.php?category_id=$category_id&subcategory_id=$subcategory_id&post_id=$post_id");
        exit;
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
    $limit = 5;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

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


    // Calculate total number of pages
    $total_comments_query = "SELECT COUNT(*) as total FROM comments WHERE post_id = ?";
    $stmt = mysqli_prepare($connection, $total_comments_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_comments = mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);

    // echo $total_comments;
    $total_pages = ceil($total_comments / $limit);
}

// Determine the position of the current post in the $posts array
$current_post_index = array_search($post_id, array_column($posts, 'id'));

// Determine previous and next post IDs if they exist
$prev_post_id = $current_post_index > 0 ? $posts[$current_post_index - 1]['id'] : null;
$next_post_id = $current_post_index !== false && $current_post_index < count($posts) - 1 ? $posts[$current_post_index + 1]['id'] : null;

// Fetch the previous subcategory's last post if on the first post of the current subcategory
$prev_subcategory_id = null;
$prev_subcategory_last_post_id = null;
if ($current_post_index === 0 && !empty($subcategories)) {
    $prev_subcategory_index = array_search($subcategory_id, array_column($subcategories, 'id')) - 1;
    if ($prev_subcategory_index >= 0) {
        $prev_subcategory_id = $subcategories[$prev_subcategory_index]['id'];
        $prev_posts_query = "SELECT id FROM posts WHERE subcategory_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = mysqli_prepare($connection, $prev_posts_query);
        mysqli_stmt_bind_param($stmt, "i", $prev_subcategory_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $prev_post = mysqli_fetch_assoc($result);
        $prev_subcategory_last_post_id = $prev_post['id'];
        mysqli_stmt_close($stmt);
    }
}

// Fetch the next subcategory's first post if on the last post of the current subcategory
$next_subcategory_id = null;
$next_subcategory_first_post_id = null;
if ($current_post_index === count($posts) - 1 && !empty($subcategories)) {
    $next_subcategory_index = array_search($subcategory_id, array_column($subcategories, 'id')) + 1;
    if ($next_subcategory_index < count($subcategories)) {
        $next_subcategory_id = $subcategories[$next_subcategory_index]['id'];
        $next_posts_query = "SELECT id FROM posts WHERE subcategory_id = ? ORDER BY id ASC LIMIT 1";
        $stmt = mysqli_prepare($connection, $next_posts_query);
        mysqli_stmt_bind_param($stmt, "i", $next_subcategory_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $next_post = mysqli_fetch_assoc($result);
        $next_subcategory_first_post_id = $next_post['id'] ?? null;
        mysqli_stmt_close($stmt);
    }
}

// Handle comment form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $comment = $_POST['comment'];

        if (isset($_GET['edit_comment_id'])) {
            $edit_comment_id = (int) $_GET['edit_comment_id'];
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

        header("Location: post.php?category_id=$category_id&subcategory_id=$subcategory_id&post_id=$post_id");
        exit;
    } else {
        header("Location: login.php");
        exit;
    }
}

// Handle comment deletion
if (isset($_GET['delete_comment_id']) && isset($_SESSION['user_id'])) {
    $delete_comment_id = (int) $_GET['delete_comment_id'];
    $user_id = $_SESSION['user_id'];
    $delete_comment_query = "DELETE FROM comments WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connection, $delete_comment_query);
    mysqli_stmt_bind_param($stmt, "ii", $delete_comment_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "<script>alert('Are you sure you want to delete this comment?');</script>";
    header("Location: post.php?category_id=$category_id&subcategory_id=$subcategory_id&post_id=$post_id");
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
    <link rel="stylesheet" href="common.css">

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

        /* Ensure you include Font Awesome */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

        .sidebar h3 {
            color: #ff6600;
            /* Color for the subcategory headings */
            font-weight: bold;
            margin-top: 20px;
            display: flex;
            align-items: center;
        }

        .sidebar h3 .fas {
            margin-right: 8px;
            /* Space between icon and text */
        }

        .sidebar ul.list-group {
            margin-bottom: 20px;
        }

        .sidebar li.list-group-item {
            list-style-type: none;
            padding: 5px 10px;
            display: flex;
            align-items: center;
            border: 0;
            background-color: re;
            background-color: #f8f9fa;

        }

        .sidebar li.list-group-item .fas {
            margin-right: 8px;
            /* Space between icon and text */
        }

        .sidebar li.list-group-item a {
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            transform: translateX(-4px);
            /* padding-right    : 10px */
            /* align-items: center; */
        }

        .sidebar li.list-group-item a span {

            transform: translateX(2px);
            /* padding-right    : 10px */
            /* align-items: center; */
        }

        .sidebar li.list-group-item a:hover {
            text-decoration: underline;
            color: blue;
        }

        .sidebar li.list-group-item.active {
            /* background-color: #f8f9fa; */
            /* Background color for active items */
        }

        .sidebar li.list-group-item.active a {
            color: #007bff;
            /* Color for active item links */
            font-weight: bold;
        }

        .language-javascript {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }
    </style>


</head>

<body>

    <?php include 'header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 sidebar">
                <?php foreach ($subcategories as $subcategory): ?>
                    <h3>
                        <i class="fas fa-book"></i> <!-- Example icon for subcategories -->
                        <?php echo htmlspecialchars($subcategory['subcategory_name']); ?>
                    </h3>
                    <ul class="list-group">
                        <?php
                        // Fetch posts for the current subcategory
                        $posts_query = "SELECT id, title FROM posts WHERE subcategory_id = ?";
                        $stmt = mysqli_prepare($connection, $posts_query);
                        mysqli_stmt_bind_param($stmt, "i", $subcategory['id']);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
                        mysqli_stmt_close($stmt);

                        foreach ($posts as $post): ?>
                            <li class="list-group-item">
                                <a
                                    href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory['id']; ?>&post_id=<?php echo $post['id']; ?>">
                                    <i class="fas fa-file-alt"></i> <!-- Example icon for posts -->
                                    <span>
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            </div>


            <div class="col-md-9 main-content">
                <?php if ($post_id): ?>
                    <h2><?php echo htmlspecialchars($current_post['title']); ?></h2>
                    <div class="content">
                        <?php echo (($current_post['content'])); ?>
                    </div>
                    <div class="views">
                        <p>Views: <?php echo $current_post['views']; ?></p>
                    </div>

                    <div class="prev-next-buttons">
                        <?php if ($current_post_index === 0 && $prev_subcategory_last_post_id): ?>
                            <a class="btn-nav"
                                href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $prev_subcategory_id; ?>&post_id=<?php echo $prev_subcategory_last_post_id; ?>">Previous</a>
                        <?php elseif ($prev_post_id): ?>
                            <a class="btn-nav"
                                href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory_id; ?>&post_id=<?php echo $prev_post_id; ?>">Previous</a>
                        <?php endif; ?>

                        <?php if ($next_post_id): ?>
                            <a class="btn-nav"
                                href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory_id; ?>&post_id=<?php echo $next_post_id; ?>">Next</a>
                        <?php elseif ($next_subcategory_first_post_id): ?>
                            <a class="btn-nav"
                                href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $next_subcategory_id; ?>&post_id=<?php echo $next_subcategory_first_post_id; ?>">Next</a>
                        <?php endif; ?>
                    </div>

                    <div class="share-buttons">
                        <h4>Share this post:</h4>
                        <!-- Facebook -->
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://yourwebsite.com/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>"
                            target="_blank" class="btn btn-primary">
                            <i class="fab fa-facebook-f"></i> Share on Facebook
                        </a>

                        <!-- Twitter -->
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://yourwebsite.com/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>&text=<?php echo urlencode($current_post['title']); ?>"
                            target="_blank" class="btn btn-info">
                            <i class="fab fa-twitter"></i> Share on Twitter
                        </a>

                        <!-- Instagram -->
                        <a href="http"></a>

                        <!-- WhatsApp -->
                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode('localhost/tutorial-test/My/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>"
                            target="_blank" class="btn btn-success">
                            <i class="fab fa-whatsapp"></i> Share on WhatsApp
                        </a>
                    </div>


                    <div class="comments">
                        <h3>Comments (<?php echo $total_comments; ?>) </h3>
                        <?php if (isset($_SESSION['username'])): ?>
                            <form method="POST">
                                <div class="form-group">
                                    <textarea name="comment" class="form-control" rows="3" placeholder="Leave a comment..."><?php if (isset($_GET['edit_comment_id'])) {
                                        echo htmlspecialchars($comments[array_search((int) $_GET['edit_comment_id'], array_column($comments, 'id'))]['comment']);
                                    } ?></textarea>
                                </div>
                                <!-- reCAPTCHA widget -->
                                <div class="g-recaptcha" data-sitekey="your-site-key"></div>

                                <button type="submit"
                                    class="btn btn-primary mt-2"><?php echo isset($_GET['edit_comment_id']) ? 'Update Comment' : 'Post Comment'; ?></button>
                            </form>

                            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                        <?php else: ?>
                            <p>Please <a href="login.php">login</a> to post comments.</p>
                        <?php endif; ?>
                        <ul class="list-group mt-3">
                            <!-- if comments more than 5 add load more  -->




                            <?php foreach ($comments as $comment): ?>

                                <li class=" list-group-item">
                                    <strong><?php echo htmlspecialchars($comment['username']); ?></strong>


                                    <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    <small><?php echo htmlspecialchars(date('m/d/Y', strtotime($comment['created_at']))); ?></small>

                                    <?php if ($comment['user_id'] == isset($_SESSION['user_id'])): ?>
                                        <a href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory_id; ?>&post_id=<?php echo $post_id; ?>&edit_comment_id=<?php echo $comment['id']; ?>"
                                            class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <a href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory_id; ?>&post_id=<?php echo $post_id; ?>&delete_comment_id=<?php echo $comment['id']; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this comment?');">Delete</a>
                                    <?php endif; ?>
                                </li>

                            <?php endforeach; ?>

                        </ul>
                    </div>


                    <nav aria-label="Page navigation">
                        <ul class="pagination mt-3">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory_id; ?>&post_id=<?php echo $post_id; ?>&page=<?php echo $page - 1; ?>"
                                        aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory_id; ?>&post_id=<?php echo $post_id; ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory_id; ?>&post_id=<?php echo $post_id; ?>&page=<?php echo $page + 1; ?>"
                                        aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>

                <?php endif; ?>
            </div>
        </div>
    </div>


    <?php require_once 'footer.php'; ?>

    <script>
        function copyToClipboard() {
            const codeElement = document.querySelector('.language-javascript');
            const copyBtn = document.createElement('button');
            copyBtn.textContent = 'Copy';
            copyBtn.addEventListener('click', () => {
                navigator.clipboard.writeText(codeElement.textContent);
                copyBtn.textContent = 'Copied!';
                setTimeout(() => {
                    copyBtn.textContent = 'Copy';
                }, 2000);
            });
            codeElement.appendChild(copyBtn);

        }
        copyToClipboard()
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>

</html>