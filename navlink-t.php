<?php
// Assuming a connection to the database is already established
session_start();
require 'db.php';
// Handle adding/editing/deleting navlinks
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_navlink'])) {
        $name = $_POST['name'];
        $url = $_POST['url'];
        $category_id = $_POST['category_id'];

        $stmt = $connection->prepare("INSERT INTO navlinks (name, url, category_id) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $name, $url, $category_id);
        $stmt->execute();
    } elseif (isset($_POST['edit_navlink'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $url = $_POST['url'];

        $stmt = $connection->prepare("UPDATE navlinks SET name = ?, url = ? WHERE id = ?");
        $stmt->bind_param('ssi', $name, $url, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete_navlink'])) {
        $id = $_POST['id'];

        $stmt = $connection->prepare("DELETE FROM navlinks WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}

// Fetch categories and their navlinks
$categories_result = $connection->query("SELECT * FROM categories");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

foreach ($categories as &$category) {
    $stmt = $connection->prepare("SELECT * FROM navlinks WHERE category_id = ?");
    $stmt->bind_param('i', $category['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $category['navlinks'] = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<h2>Manage Navigation Links</h2>

<?php foreach ($categories as $category): ?>
    <h3><?php echo htmlspecialchars($category['category_name']); ?> Navigation Links</h3>
    <form method="POST">
        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
        <input type="text" name="name" placeholder="NavLink Name" required>
        <input type="text" name="url" placeholder="NavLink URL" required>
        <button type="submit" name="add_navlink">Add NavLink</button>
    </form>
    <ul>
        <?php foreach ($category['navlinks'] as $navlink): ?>
            <li>
                <?php echo htmlspecialchars($navlink['name']); ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $navlink['id']; ?>">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($navlink['name']); ?>" required>
                    <input type="text" name="url" value="<?php echo htmlspecialchars($navlink['url']); ?>" required>
                    <button type="submit" name="edit_navlink">Edit</button>
                    <button type="submit" name="delete_navlink">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endforeach; ?>