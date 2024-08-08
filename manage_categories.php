<?php
session_start();
require_once 'db.php';

// Check if the user is logged in and has the right role
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Handle category update
if (isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['category_name']);
    $category_image = $_FILES['category_image'];

    if (empty($category_name)) {
        $error = "Category name is required.";
    } else {
        $stmt = mysqli_prepare($connection, "UPDATE categories SET category_name = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $category_name, $category_id);
        mysqli_stmt_execute($stmt);

        if ($category_image['error'] != UPLOAD_ERR_NO_FILE) {
            if (!in_array($category_image['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
                $error = "Invalid image format. Only JPG, PNG, and GIF are allowed.";
            } elseif ($category_image['size'] > 2 * 1024 * 1024) { // 2MB limit
                $error = "Image size should not exceed 2MB.";
            } else {
                $image_path = 'uploads/' . basename($category_image['name']);
                if (move_uploaded_file($category_image['tmp_name'], $image_path)) {
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
// Fetch categories and subcategories
$categories = [];
$query = "SELECT c.id as category_id, c.category_name, c.category_image, s.id as subcategory_id, s.subcategory_name
          FROM categories c
          LEFT JOIN subcategories s ON c.id = s.category_id";
$result = mysqli_query($connection, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $categories[$row['category_id']]['name'] = $row['category_name'];
    $categories[$row['category_id']]['image'] = $row['category_image'];
    $categories[$row['category_id']]['subcategories'][] = [
        'id' => $row['subcategory_id'],
        'name' => $row['subcategory_name']
    ];
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Categories and Subcategories</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #3c41a1;
            padding: 20px;
        }

        .wrapper {
            width: 1000px;
            padding: 40px 30px 50px 30px;
            background: #fff;
            border-radius: 5px;
            text-align: center;
            box-shadow: 10px 10px 15px rgba(0, 0, 0, 0.1);
        }

        .wrapper header {
            font-size: 35px;
            font-weight: 600;
            margin-bottom: 20px;
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
    </style>
</head>

<body>
    <div class="wrapper">
        <header>Manage Categories and Subcategories</header>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="alert"><?php echo $success; ?></div>
        <?php endif; ?>
        <table>
            <tr>
                <th>Category Name</th>
                <th>Category Image</th>
                <th>Subcategories</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($categories as $category_id => $category): ?>
                <tr>
                    <td><?php echo $category['name']; ?></td>
                    <td><img src="<?php echo $category['image']; ?>" alt="Category Image" width="50"></td>
                    <td>
                        <ul>
                            <?php if (!empty($category['subcategories'])): ?>
                                <?php foreach ($category['subcategories'] as $subcategory): ?>
                                    <li>
                                        <?php echo $subcategory['name']; ?>
                                        <form action="manage_categories.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="subcategory_id" value="<?php echo $subcategory['id']; ?>">
                                            <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                                            <input type="text" name="subcategory_name" value="<?php echo $subcategory['name']; ?>"
                                                required>
                                            <input type="submit" name="update_subcategory" value="Update">
                                            <input type="submit" name="delete_subcategory" value="Delete">
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        <form action="manage_categories.php" method="POST">
                            <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                            <input type="text" name="subcategory_name" placeholder="New Subcategory Name" required>
                            <input type="submit" name="add_subcategory" value="Add Subcategory">
                        </form>
                    </td>
                    <td>
                        <form action="manage_categories.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                            <input type="text" name="category_name" value="<?php echo $category['name']; ?>" required>
                            <input type="file" name="category_image">
                            <input type="submit" name="update_category" value="Update">
                            <input type="submit" name="delete_category" value="Delete">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="sign-txt"><a href="dashboard.php">Back to Dashboard</a></div>
    </div>

    <script>
        const form = document.querySelectorAll("form");

        // on submit form refresh page after completing it's work
        form.forEach(form => {
            form.addEventListener("submit", event => {
                form.submit();
                setTimeout(() => {
                    window.location.reload();
                }, 2000)
                // event.preventDefault();
            });
        });


    </script>
</body>

</html>