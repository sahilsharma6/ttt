<?php
session_start();
require 'db.php';

// Check if the user is logged in and has the right role
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Fetch categories from the database
$categories = [];
$result = mysqli_query($connection, "SELECT id, category_name FROM categories");
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category_id'];

    if (!empty($title) && !empty($content) && !empty($category_id)) {
        // Prepare an insert statement
        $stmt = mysqli_prepare($connection, "INSERT INTO posts (title, content, category_id) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $category_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Post added successfully!";
        } else {
            $error = "Something went wrong. Please try again.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $error = "Please fill in all fields.";
    }
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Post</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include a WYSIWYG editor like CKEditor -->
    <script src="https://cdn.ckeditor.com/4.17.1/standard/ckeditor.js"></script>
</head>

<body>
    <?php include_once 'sidebar.php'; ?>
    <div class="dash-content">
        <h2>Add New Post</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form action="AddPost.php" method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                <script>
                    CKEDITOR.replace('content');
                </script>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Post</button>
        </form>
    </div>
</body>

</html>