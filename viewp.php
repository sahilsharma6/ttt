<?php
session_start();
require 'db.php';

// Debugging: Check if the category_id is being received
if (!isset($_GET['category_id'])) {
    echo "Category ID not set.";

}


if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    // header("Location: login.php");

} else {

    $role = $_SESSION['role'];
    $username = $_SESSION['username'];
    echo $username;
    echo $role;
}

// Now you can use $userId as needed in your index.php
// echo "Welcome, User ID: ";

if ($_GET['category_id'] == 1) {
    echo 'hlo';
    echo '<h1>Posts from category 2</h1>';
}

$category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 1;
if ($category_id === 0) {
    echo "Invalid category ID.";
    exit();
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 1; // Number of posts per page
$offset = ($page - 1) * $limit;

$all_categories_query = "SELECT id, category_name FROM categories";
$all_categories_result = mysqli_query($connection, $all_categories_query);
$categories = mysqli_fetch_all($all_categories_result, MYSQLI_ASSOC);
// Fetch category name
$category_query = mysqli_prepare($connection, "SELECT category_name FROM categories WHERE id = ?");
mysqli_stmt_bind_param($category_query, "i", $category_id);
mysqli_stmt_execute($category_query);
mysqli_stmt_bind_result($category_query, $category_name);
mysqli_stmt_fetch($category_query);
mysqli_stmt_close($category_query);

// Debugging: Check if category name is fetched
if (empty($category_name)) {
    echo "Category not found for ID: $category_id.";
    exit();
}

// Fetch posts for the category
$query = "SELECT id, title, content FROM posts WHERE category_id = ? LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "iii", $category_id, $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch total number of posts
$count_query = mysqli_prepare($connection, "SELECT COUNT(*) FROM posts WHERE category_id = ?");
mysqli_stmt_bind_param($count_query, "i", $category_id);
mysqli_stmt_execute($count_query);
mysqli_stmt_bind_result($count_query, $total_posts);
mysqli_stmt_fetch($count_query);
mysqli_stmt_close($count_query);

$total_pages = ceil($total_posts / $limit);




mysqli_close($connection);
?>

<!DOCTYPE html>
<html>

<head>
    <title><?php echo htmlspecialchars($category_name); ?></title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    foreach ($categories as $category) {
        echo '<a href="viewp.php?category_id=' . $category['id'] . '">' . htmlspecialchars($category['category_name']) . '</a><br>';
    }
    ?>
    <h1><?php echo htmlspecialchars($category_name); ?></h1>
    <div class="posts">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                    <p><?php echo nl2br(($post['content'])); ?></p>
                    <p><?php echo nl2br(($post['id'])); ?></p>
                </div>

                <?php
                require 'db.php';
                $post_id = $post['id'];
                $comments_query = "SELECT comments.comment, comments.created_at 
                                       FROM comments 
                                    --    JOIN user ON comments.user_id = testt.id 
                                       WHERE comments.post_id = ?";
                $stmt = mysqli_prepare($connection, $comments_query);
                mysqli_stmt_bind_param($stmt, "i", $post_id);
                mysqli_stmt_execute($stmt);
                $comments_result = mysqli_stmt_get_result($stmt);
                $comments = mysqli_fetch_all($comments_result, MYSQLI_ASSOC);
                mysqli_stmt_close($stmt);
                mysqli_close($connection);
                ?>

                <div class="comments">
                    <h3>Comments</h3>
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                <p><?php echo htmlspecialchars($comment['created_at']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No comments yet. Be the first to comment!</p>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="comment.php" method="POST">
                        <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <textarea name="content" required></textarea><br>
                        <button type="submit">Add Comment</button>
                    </form>
                <?php else: ?>
                    <p>You need to <a href="login.php">login</a> to comment.</p>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No posts available in this category.</p>
        <?php endif; ?>
    </div>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?category_id=<?php echo $category_id; ?>&page=<?php echo $page - 1; ?>">Previous</a>
        <?php endif; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?category_id=<?php echo $category_id; ?>&page=<?php echo $page + 1; ?>">Next</a>
        <?php endif; ?>
    </div>



</body>

</html>