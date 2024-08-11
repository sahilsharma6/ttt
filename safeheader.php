<?php

// session_start(); 
require 'db.php';

// Fetch categories from the database
$categories = [];
$category_query = "SELECT id, category_name, category_image FROM categories";
$category_result = mysqli_query($connection, $category_query);
if ($category_result) {
    $categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
} else {
    echo "Error fetching categories: " . mysqli_error($connection);
}

// Initialize variables for navlinks
$navlinks = [];
$current_category = null;

// Check if a category_id is provided
if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);

    // Fetch the navlinks for the selected category
    $stmt = $connection->prepare("SELECT * FROM navlinks WHERE category_id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $navlinks = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch the current category details
    $stmt = $connection->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $category_result = $stmt->get_result();
    $current_category = $category_result->fetch_assoc();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['username']);
$username = $is_logged_in ? htmlspecialchars($_SESSION['username']) : '';

?>


<header class="bg-light py-3 position-relative">
    <div class="container-fluid d-flex justify-content-align-items-center">
        <a class="navbar-brand" href="./check.php">
            <img src="uploads/logo.png" height="80" alt="logo">
        </a>
        <form class="d-flex" method="POST" action="search.php" role="search" style="margin-right: 100px">
            <input class="form-control me-2 search" type="search" name="q" placeholder="Search" aria-label="Search" />
        </form>
        <div class="position-absolute login">
            <?php if ($is_logged_in): ?>
                <a href="logout.php" style="text-decoration: none;">logout</a>
            <?php else: ?>
                <a href="login.php" style="text-decoration: none;">login</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<nav class="mynav">
    <ul>
        <?php if ($current_category === null): ?>
            <!-- Display all categories -->
            <?php foreach ($categories as $category): ?>
                <a href="?category_id=<?php echo $category['id']; ?>">
                    <li>
                        <div>
                            <img src="<?php echo $category['category_image']; ?>" height="20"
                                alt="<?php echo htmlspecialchars($category['category_name']); ?>">
                            <span class="na"><?php echo htmlspecialchars($category['category_name']); ?></span>
                        </div>
                    </li>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Display only the selected category -->
            <a href="?">
                <li>
                    <div>
                        <img src="<?php echo $current_category['category_image']; ?>" height="20"
                            alt="<?php echo htmlspecialchars($current_category['category_name']); ?>">
                        <span class="na"><?php echo htmlspecialchars($current_category['category_name']); ?></span>
                    </div>
                </li>
            </a>
        <?php endif; ?>
    </ul>
</nav>

<?php if ($current_category !== null): ?>
    <div class="mynav">
        <h1><?php echo htmlspecialchars($current_category['category_name']); ?> Navigation Links</h1>
        <ul>
            <?php if (!empty($navlinks)): ?>
                <?php foreach ($navlinks as $navlink): ?>
                    <?php
                    $navlink_id = substr($navlink['url'], -2);

                    $stmt = $connection->prepare("SELECT * FROM categories WHERE id = ?");
                    $stmt->bind_param('i', $navlink_id);
                    $stmt->execute();
                    $category_result = $stmt->get_result();
                    $category = $category_result->fetch_assoc();

                    // echo '<li><img src="' . $category['category_image'] . '" alt=""></li>';
                    // $category_img = $category['category_image'];
                    echo $navlink_id;
                    ?>
                    <li><img src="<?php echo $category['category_image']; ?>" height="30" alt=""></li>
                    <li><a href="<?php echo htmlspecialchars($navlink['url']); ?>">
                            <?php echo htmlspecialchars($navlink['name']); ?>
                        </a></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No navigation links available for this category.</li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>