<?php
session_start();
require 'db.php';

// Check if the user is logged in and has the right role
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Directory for uploaded images
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle category update
if (isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['category_name']);
    $category_image = $_FILES['category_image'];

    if (empty($category_name)) {
        $error = "Category name is required.";
    } else {
        // Update category name
        $stmt = mysqli_prepare($connection, "UPDATE categories SET category_name = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $category_name, $category_id);
        mysqli_stmt_execute($stmt);

        // Handle image upload
        if ($category_image['error'] != UPLOAD_ERR_NO_FILE) {
            if (!in_array($category_image['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
                $error = "Invalid image format. Only JPG, PNG, and GIF are allowed.";
            } elseif ($category_image['size'] > 2 * 1024 * 1024) { // 2MB limit
                $error = "Image size should not exceed 2MB.";
            } else {
                $image_path = $upload_dir . basename($category_image['name']);
                if (move_uploaded_file($category_image['tmp_name'], $image_path)) {
                    // Update category image
                    $stmt = mysqli_prepare($connection, "UPDATE categories SET category_image = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "si", $image_path, $category_id);
                    mysqli_stmt_execute($stmt);
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }

        if (empty($error)) {
            $success = "Category updated successfully!";
        }
    }
}

// Handle category deletion
if (isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];
    $stmt = mysqli_prepare($connection, "DELETE FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $success = "Category deleted successfully!";
}

// Handle subcategory update
if (isset($_POST['update_subcategory'])) {
    $subcategory_id = $_POST['subcategory_id'];
    $subcategory_name = trim($_POST['subcategory_name']);

    if (empty($subcategory_name)) {
        $error = "Subcategory name is required.";
    } else {
        $stmt = mysqli_prepare($connection, "UPDATE subcategories SET subcategory_name = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $subcategory_name, $subcategory_id);
        mysqli_stmt_execute($stmt);

        if (empty($error)) {
            $success = "Subcategory updated successfully!";
        }
    }
}

// Handle subcategory deletion
if (isset($_POST['delete_subcategory'])) {
    $subcategory_id = $_POST['subcategory_id'];
    $stmt = mysqli_prepare($connection, "DELETE FROM subcategories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $subcategory_id);
    mysqli_stmt_execute($stmt);
    $success = "Subcategory deleted successfully!";
}

// Handle subcategory addition
if (isset($_POST['add_subcategory'])) {
    $category_id = $_POST['category_id'];
    $subcategory_name = trim($_POST['subcategory_name']);

    if (empty($subcategory_name)) {
        $error = "Subcategory name is required.";
    } else {
        $stmt = mysqli_prepare($connection, "INSERT INTO subcategories (subcategory_name, category_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "si", $subcategory_name, $category_id);
        mysqli_stmt_execute($stmt);

        if (empty($error)) {
            $success = "Subcategory added successfully!";
        }
    }
}

// Pagination and filtering logic
$category_filter = isset($_GET['category_filter']) ? $_GET['category_filter'] : '';
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
$items_per_page = 1;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Fetch categories and subcategories with pagination, filtering, and search
// Fetch only the categories with pagination and search
$categories = [];
$where_clause = "WHERE category_name LIKE ?";
$query = "SELECT id as category_id, category_name, category_image
          FROM categories
          $where_clause
          LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($connection, $query);
$search_term = '%' . $search_query . '%';
mysqli_stmt_bind_param($stmt, "sii", $search_term, $items_per_page, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $categories[$row['category_id']] = [
        'name' => $row['category_name'],
        'image' => $row['category_image'],
        'subcategories' => []
    ];
}

if (!empty($categories)) {
    $category_ids = array_keys($categories);
    $in_clause = implode(',', array_fill(0, count($category_ids), '?'));
    $query = "SELECT id as subcategory_id, subcategory_name, category_id
              FROM subcategories
              WHERE category_id IN ($in_clause)";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, str_repeat('i', count($category_ids)), ...$category_ids);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $categories[$row['category_id']]['subcategories'][] = [
            'id' => $row['subcategory_id'],
            'name' => $row['subcategory_name']
        ];
    }
}


// Get the total number of categories for pagination
$count_query = "SELECT COUNT(DISTINCT c.id) as total_categories FROM categories c";
$count_query .= " WHERE c.category_name LIKE ?";
$count_stmt = mysqli_prepare($connection, $count_query);
mysqli_stmt_bind_param($count_stmt, "s", $search_term);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_categories = mysqli_fetch_assoc($count_result)['total_categories'];
$total_pages = ceil($total_categories / $items_per_page);

mysqli_close($connection);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Categories and Subcategories</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .alert {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 1s;
            margin-bottom: 20px;
        }

        .alert.error {
            background-color: #dc3545;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        form {
            margin: 20px 0;
        }

        form .field {
            margin-bottom: 20px;
        }

        form .field input,
        form .field select {
            width: calc(100% - 30px);
            padding: 10px;
            font-size: 18px;
            border-radius: 5px;
            border: 1px solid #bfbfbf;
        }

        form input[type="submit"] {
            padding: 10px 20px;
            background-color: #5372F0;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        form input[type="submit"]:hover {
            background-color: #2c52ed;
        }

        .sign-txt a {
            text-decoration: none;
            color: #5372F0;
        }

        .sign-txt a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <?php include_once 'sidebar.php'; ?>

    <div class="dash-content">
        <h2>Manage Categories and Subcategories</h2>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="alert"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-between mb-4">
            <div>
                <form class="d-flex" method="GET" action="">
                    <input type="text" name="search_query" class="form-control me-2"
                        placeholder="Search by category name" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>

        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Category ID</th>
                    <th>Category Name</th>
                    <th>Category Image</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category_id => $category): ?>
                    <tr>
                        <td><?php echo $category_id; ?></td>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td>
                            <?php if ($category['image']): ?>
                                <img src="<?php echo htmlspecialchars($category['image']); ?>" alt="Category Image"
                                    style="width: 50px; height: 50px;">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="" enctype="multipart/form-data" style="display: inline-block;">
                                <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                                <input type="text" name="category_name"
                                    value="<?php echo htmlspecialchars($category['name']); ?>">
                                <input type="file" name="category_image">
                                <button type="submit" name="update_category" class="btn btn-primary">Update</button>
                            </form>
                            <form method="POST" action="" style="display: inline-block;">
                                <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                                <button type="submit" name="delete_category" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php if (!empty($category['subcategories'])): ?>
                        <?php foreach ($category['subcategories'] as $subcategory): ?>
                            <tr>
                                <td colspan="3"><b>Subcategories </b>— <?php echo htmlspecialchars($subcategory['name']); ?></td>
                                <td>
                                    <form method="POST" action="" style="display: inline-block;">
                                        <input type="hidden" name="subcategory_id" value="<?php echo $subcategory['id']; ?>">
                                        <input type="text" name="subcategory_name" class="mx-2"
                                            value="<?php echo htmlspecialchars($subcategory['name']); ?> ">
                                        <button type="submit" name="update_subcategory" class="btn btn-primary mx-2">Update</button>
                                    </form>
                                    <form method="POST" action="" style="display: inline-block;">
                                        <input type="hidden" name="subcategory_id" value="<?php echo $subcategory['id']; ?>">
                                        <button type="submit" name="delete_subcategory" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr>
                        <td colspan="4">
                            <form method="POST" action="" class="d-flex">
                                <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                                <input type="text" name="subcategory_name" placeholder="New Subcategory Name" required>
                                <button type="submit" name="add_subcategory" class="btn btn-success mx-2">Add
                                    Subcategory</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination logic and display -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="?page=<?php echo $page - 1; ?>&search_query=<?php echo urlencode($search_query); ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $i; ?>&search_query=<?php echo urlencode($search_query); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="?page=<?php echo $page + 1; ?>&search_query=<?php echo urlencode($search_query); ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</body>

</html>