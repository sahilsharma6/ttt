<?php

// session_start(); 
require 'db.php';

// Fetch categories from the database
$categories = [];
$category_query = "SELECT id, category_name, category_image FROM categories";
$category_result = mysqli_query($connection, $category_query);
if ($category_result) {
    $categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
}
if (!$category_result) {
    echo "Error fetching categories: " . mysqli_error($connection);
}

$is_logged_in = isset($_SESSION['username']);
$username = $is_logged_in ? htmlspecialchars($_SESSION['username']) : '';

// This line prevents the warning by initializing $categories if it's not set
?>

<header class="bg-light py-3 position-relative ">
    <div class="container-fluid d-flex justify-content- align-items-center">
        <a class="navbar-brand" href="./check.php">
            <img src="uploads/logo.png" height="80" alt="logo">
        </a>
        <form class="d-flex" method="POST" action="search.php" role="search" style="margin-right: 100px">
            <!-- <input class="form-control me-2" type="search" name="q" placeholder="Search" aria-label="Search" /> -->
            <div class="d-flex search border p-2">
                <input class="form-control me-2 " type="search" name="q" placeholder="Search" aria-label="Search" />
                <button class="btn btn-outline-dark " type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <!-- <button class="btn btn-outline-success" type="submit">Search</button> -->
        </form>
        <div id="google_translate_element"></div>

        <script type="text/javascript">
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({ pageLanguage: 'en' }, 'google_translate_element');
            }
        </script>

        <script type="text/javascript"
            src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

        <div class="position-absolute login">
            <?php if ($is_logged_in): ?>
                <!-- <span> <?php echo $username; ?></span> -->
                <a href="logout.php" style="text-decoration: none;">logout</a>

            <?php else: ?>
                <a href="login.php" style="text-decoration: none;">login</a>
            <?php endif; ?>
        </div>
    </div>
</header>





<nav class="mynav">
    <ul>
        <?php foreach ($categories as $category): ?>
            <a href="post.php?category_id=<?php echo $category['id']; ?>">
                <li class="">
                    <div class="">

                        <img src="<?php echo $category['category_image']; ?>" height="20"
                            alt="<?php echo htmlspecialchars($category['category_name']); ?>">
                        <span class="na">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </span>
                    </div>
                </li>
            </a>
        <?php endforeach; ?>
    </ul>
</nav>