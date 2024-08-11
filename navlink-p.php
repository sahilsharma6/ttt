<?php

session_start();
require 'db.php';

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);

    // Fetch the navlinks for the selected category
    $stmt = $connection->prepare("SELECT * FROM navlinks WHERE category_id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $navlinks = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch the posts for the selected category
    $stmt = $connection->prepare("SELECT * FROM posts WHERE category_id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="category-navlinks">
    <ul>
        <?php foreach ($navlinks as $navlink): ?>
            <li><a
                    href="<?php echo htmlspecialchars($navlink['url']); ?>"><?php echo htmlspecialchars($navlink['name']); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="posts">
    <?php if (isset($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <h2><?php echo htmlspecialchars($post['title']); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts found for this category.</p>
    <?php endif; ?>
</div>