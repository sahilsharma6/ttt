<?php session_start();
require 'db.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in and is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'SuperAdmin') {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Handle comment deletion
if (isset($_GET['delete_comment_id'])) {
    $comment_id = (int) $_GET['delete_comment_id'];

    $delete_query = mysqli_prepare($connection, "DELETE FROM comments WHERE id = ?");
    mysqli_stmt_bind_param($delete_query, "i", $comment_id);
    if (mysqli_stmt_execute($delete_query)) {
        echo "Comment deleted successfully.";
    } else {
        echo "Failed to delete comment.";
    }
    mysqli_stmt_close($delete_query);
}

// Sorting and searching parameters
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$search_keyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '';

// Fetch all comments with details
$comments_query = "
SELECT comments.id, comments.comment, comments.created_at, testt.username, posts.title
FROM comments
JOIN testt ON comments.user_id = testt.id
JOIN posts ON comments.post_id = posts.id
WHERE posts.title LIKE ?
ORDER BY comments.created_at $sort_order
";
$search_keyword = '%' . $search_keyword . '%';
$comments_stmt = mysqli_prepare($connection, $comments_query);
mysqli_stmt_bind_param($comments_stmt, "s", $search_keyword);
mysqli_stmt_execute($comments_stmt);
$comments_result = mysqli_stmt_get_result($comments_stmt);

if (!$comments_result) {
    die("Query Failed: " . mysqli_error($connection));
}

$comments = mysqli_fetch_all($comments_result, MYSQLI_ASSOC);

// Debug: Print fetched comments
echo "
<pre>";
// print_r($comments);
echo "</pre>";

mysqli_close($connection);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin - Manage Comments</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /*  */
    </style>
</head>

<body>

    <div class="wrapper" style="background:; width: 100vw; height: ;">

        <?php include_once 'sidebar.php'; ?>

        <div class="dash-content">
            <!-- Search Form -->
            <form class="form-inline my-3 d-flex  align-items-center gap-2 " method="GET" action="">
                <input type="text" name="search_keyword" class="form-control my-2 " placeholder="Search by post title"
                    style="height:40px; width:250px"
                    value="<?php echo htmlspecialchars(isset($_GET['search_keyword']) ? $_GET['search_keyword'] : ''); ?>">
                <select name="sort_order" class="form-control mr-2" style="height:40px; width:250px">
                    <option value="DESC" <?php echo (isset($_GET['sort_order']) && $_GET['sort_order'] === 'DESC') ? 'selected' : ''; ?>>Latest</option>
                    <option value="ASC" <?php echo (isset($_GET['sort_order']) && $_GET['sort_order'] === 'ASC') ? 'selected' : ''; ?>>Oldest</option>
                </select>

                <button type="submit" class="btn btn-primary my-2 px-5 py-1 " style="height:39px">Filter</button>
            </form>

            <!-- <h1 class="text-center">All Comments</h1> -->
            <!-- <p class="text-center">Welcome, <?php echo htmlspecialchars($username); ?> (Superadmin)</p> -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Comment</th>
                        <th>Username</th>
                        <th>Post Title</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($comments)): ?>
                        <tr>
                            <td colspan="6">No comments found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($comment['id']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></td>
                                <td><?php echo htmlspecialchars($comment['username']); ?></td>
                                <td><?php echo htmlspecialchars($comment['title']); ?></td>
                                <td><?php echo htmlspecialchars($comment['created_at']); ?></td>
                                <td>
                                    <a href="?delete_comment_id=<?php echo $comment['id']; ?>&sort_order=<?php echo $sort_order; ?>&search_keyword=<?php echo htmlspecialchars($search_keyword); ?>"
                                        onclick="return confirm('Are you sure you want to delete this comment?');"
                                        class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>