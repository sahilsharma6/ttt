<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header('Location: login.php');
    exit();
}

$post_id = $_GET['id'];
$post = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM posts WHERE id = $post_id"));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category_id'];

    $stmt = mysqli_prepare($connection, "UPDATE posts SET title = ?, content = ?, category_id = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssii", $title, $content, $category_id, $post_id);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: posts.php');
        exit();
    } else {
        echo "Update failed.";
    }
}

$categories_result = mysqli_query($connection, "SELECT * FROM categories");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Post</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php include_once 'sidebar.php'; ?>
    <div class="dash-content">
        <h1>Edit Post</h1>
        <form action="EditPost.php?id=<?php echo $post_id; ?>" method="POST">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" name="title" id="title" class="form-control" value="<?php echo $post['title']; ?>"
                    required>
            </div>
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea name="content" id="content" class="form-control" rows="10"
                    required><?php echo $post['content']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                        <option value="<?php echo $category['id']; ?>" <?php if ($category['id'] == $post['category_id'])
                               echo 'selected'; ?>>
                            <?php echo $category['category_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Post</button>
        </form>
    </div>
</body>

</html>