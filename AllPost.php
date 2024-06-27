<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header('Location: login.php');
    exit();
}

$result = mysqli_query($connection, "SELECT posts.id, posts.title, posts.content, categories.category_name as category_name 
                                     FROM posts 
                                     JOIN categories ON posts.category_id = categories.id");

if (!$result) {
    die("Query Failed: " . mysqli_error($connection));
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
        <table class="table table-bordered text-center">
            <tr>
                <th>Title</th>
                <th>Content</th>
                <th>Category</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo substr($row['content'], 0, 100); ?>...</td>
                    <td><?php echo $row['category_name']; ?></td>
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