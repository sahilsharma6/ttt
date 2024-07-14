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
    $post_query = "SELECT id, title, content FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($connection, $post_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $current_post = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Website</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
        }

        .navbar {
            margin-bottom: 20px;
        }

        .sidebar {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .content {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">My Website</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <?php foreach (array_reverse($categories) as $category): ?>
                        <li class="nav-item">
                            <a class="nav-link"
                                href="?category_id=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></a>
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
                                href="?category_id=<?php echo $category_id; ?>&post_id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <main role="main" class="col-md-9 content">
                <?php if ($current_post): ?>
                    <h2><?php echo htmlspecialchars($current_post['title']); ?></h2>
                    <p><?php echo nl2br(($current_post['content'])); ?></p>
                    <div class="pagination">
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
                            <a class="btn btn-primary"
                                href="?category_id=<?php echo $category_id; ?>&post_id=<?php echo $next_post_id; ?>">Next
                                Post</a>
                        <?php endif; ?>
                    </div>
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