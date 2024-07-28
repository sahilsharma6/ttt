<?php

// session_start(); 
require 'db.php';

// Fetch categories from the database
$categories = [];
$category_query = "SELECT id, category_name FROM categories";
$category_result = mysqli_query($connection, $category_query);
if ($category_result) {
    $categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
}
if (!$category_result) {
    echo "Error fetching categories: " . mysqli_error($connection);
}

// This line prevents the warning by initializing $categories if it's not set
?>

<header class="bg-light py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="./check.php">
            <img src="uploads/logo.png" height="80" alt="logo">
        </a>
        <form class="d-flex" method="POST" action="search.php" role="search">
            <!-- <input class="form-control me-2" type="search" name="q" placeholder="Search" aria-label="Search" /> -->
            <input class="form-control me-2" type="search" name="q" placeholder="Search" aria-label="Search" />

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
                <?php foreach ($categories as $category): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="post.php?category_id=<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>