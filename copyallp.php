<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header('Location: login.php');
    exit();
}

$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'latest';
$category_filter = isset($_GET['category_filter']) ? $_GET['category_filter'] : '';
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';

$sql = "SELECT posts.id, posts.title, posts.content, posts.created_at, categories.category_name 
        FROM posts 
        JOIN categories ON posts.category_id = categories.id ";

$conditions = [];
if ($category_filter) {
    $conditions[] = "categories.id = $category_filter";
}
if ($search_query) {
    $conditions[] = "posts.title LIKE '%$search_query%'";
}

if (count($conditions) > 0) {
    $sql .= 'WHERE ' . implode(' AND ', $conditions) . ' ';
}

if ($sort_order == 'latest') {
    $sql .= "ORDER BY posts.created_at DESC";
} elseif ($sort_order == 'oldest') {
    $sql .= "ORDER BY posts.created_at ASC";
}

$result = mysqli_query($connection, $sql);

if (!$result) {
    die("Query Failed: " . mysqli_error($connection));
}

// Fetch all categories for the filter dropdown
$categories_result = mysqli_query($connection, "SELECT id, category_name FROM categories");
$categories = [];
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row;
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Posts</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php include_once 'sidebar.php'; ?>
    <div class="dash-content">
        <h1>Manage Posts</h1>
        <form method="GET" action="" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <select name="sort_order" class="form-select">
                        <option value="latest" <?php if ($sort_order == 'latest')
                            echo 'selected'; ?>>Latest</option>
                        <option value="oldest" <?php if ($sort_order == 'oldest')
                            echo 'selected'; ?>>Oldest</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="category_filter" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php if ($category_filter == $category['id'])
                                   echo 'selected'; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search_query" class="form-control" placeholder="Search by title"
                        value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <table class="table table-bordered text-center">
            <tr>
                <th>Title</th>
                <th>Content</th>
                <th>Category</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td>
                        <?php
                        $content = strip_tags($row['content']); // Remove any HTML tags for word count
                    
                        // Check if content length exceeds 20 words
                        if (str_word_count($content) > 20) {
                            // Limit the content to 20 words
                            $content = implode(' ', array_slice(str_word_count($content, 2), 0, 20)) . '...';
                        }

                        // Output the content with HTML preserved
                        echo nl2br($content);
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td>
                        <a href="EditPost.php?id=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="DeletePost.php?id=<?php echo $row['id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>

</html>

<?php
mysqli_close($connection);
?>