<?php
session_start();
require 'db.php';

$category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$current_post = [];
$posts = [];

if ($category_id) {
    // Fetch posts for the selected category
    $posts_query = "SELECT id, title FROM posts WHERE category_id = ?";
    $stmt = mysqli_prepare($connection, $posts_query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Fetch the first post data
    if (!empty($posts)) {
        $post_id = $posts[0]['id'];
        $post_query = "SELECT id, title, content, views FROM posts WHERE id = ?";
        $stmt = mysqli_prepare($connection, $post_query);
        mysqli_stmt_bind_param($stmt, "i", $post_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $current_post = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($connection);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category - Javatpoint Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar {
            background-color: #004d40;
        }

        .navbar-brand,
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
    </style>
</head>

<body>
    <header class="bg-light py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="#">Your Logo</a>
            <form class="d-flex" role="search">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
        </div>
    </header>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!-- Navbar items will be dynamically generated -->
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 sidebar">
                <h3><?php echo htmlspecialchars($category_id ? "Category: " . $category_id : "HTML Tutorial"); ?></h3>
                <ul class="nav flex-column">
                    <?php foreach ($posts as $post): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="post.php?post_id=<?php echo $post['id']; ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <main class="col-md-9 col-lg-10 main-content">
                <?php if ($current_post): ?>
                    <h1><?php echo htmlspecialchars($current_post['title']); ?></h1>
                    <p><?php echo $current_post['content']; ?></p>
                    <p><strong>Views: </strong><?php echo $current_post['views']; ?></p>
                <?php else: ?>
                    <p>No posts available in this category.</p>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>