<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'SuperAdmin')) {
    header('Location: login.php');
    exit();
}

// Pagination setup
$limit = 1; // Number of categories per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

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

// Handle search query for categories
$search_query = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

// Fetch categories with pagination and search filtering
$total_result = $connection->prepare("SELECT COUNT(*) AS total FROM categories WHERE category_name LIKE ?");
$total_result->bind_param('s', $search_query);
$total_result->execute();
$total_categories = $total_result->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_categories / $limit);

$categories_stmt = $connection->prepare("SELECT * FROM categories WHERE category_name LIKE ? LIMIT ? OFFSET ?");
$categories_stmt->bind_param('sii', $search_query, $limit, $offset);
$categories_stmt->execute();
$categories_result = $categories_stmt->get_result();
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

foreach ($categories as &$category) {
    $stmt = $connection->prepare("SELECT * FROM navlinks WHERE category_id = ?");
    $stmt->bind_param('i', $category['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $category['navlinks'] = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Navigation Links</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <div class="wrapper" style="width: 100vw;">
        <?php include_once 'sidebar.php'; ?>

        <div class="dash-content p-4">
            <h2 class="mb-4">Manage Navigation Links</h2>

            <!-- Search Form for Categories -->
            <form method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search Categories"
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>

            <?php foreach ($categories as $category): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="mb-0"><?php echo htmlspecialchars($category['category_name']); ?> Navigation Links</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mb-3">
                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                            <div class="input-group mb-3">
                                <input type="text" name="name" class="form-control" placeholder="NavLink Name" required>
                                <input type="text" name="url" class="form-control" placeholder="NavLink URL" required>
                                <button type="submit" name="add_navlink" class="btn btn-success">Add NavLink</button>
                            </div>
                        </form>

                        <ul class="list-group">
                            <?php if (count($category['navlinks']) > 0): ?>
                                <?php foreach ($category['navlinks'] as $navlink): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?php echo htmlspecialchars($navlink['name']); ?></span>
                                            <div>
                                                <form method="POST" class="d-inline-block">
                                                    <input type="hidden" name="id" value="<?php echo $navlink['id']; ?>">
                                                    <input type="text" name="name" class="form-control d-inline-block"
                                                        value="<?php echo htmlspecialchars($navlink['name']); ?>" required>
                                                    <input type="text" name="url" class="form-control d-inline-block"
                                                        value="<?php echo htmlspecialchars($navlink['url']); ?>" required>
                                                    <button type="submit" name="edit_navlink" class="btn btn-primary">Edit</button>
                                                    <button type="submit" name="delete_navlink"
                                                        class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item">No navigation links found.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination Links -->
            <nav aria-label="Category pagination">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="?page=<?php echo $page - 1; ?>&search=<?php echo isset($_GET['search']) ? urlencode($_GET['search']) : ''; ?>"
                                aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($i == $page)
                            echo 'active'; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $i; ?>&search=<?php echo isset($_GET['search']) ? urlencode($_GET['search']) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="?page=<?php echo $page + 1; ?>&search=<?php echo isset($_GET['search']) ? urlencode($_GET['search']) : ''; ?>"
                                aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>