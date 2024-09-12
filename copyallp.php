<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin' && $_SESSION['role'] !== 'Operator')) {
    header('Location: login.php');
    exit();
}

// Pagination settings
$posts_per_page = isset($_GET['posts_per_page']) ? (int) $_GET['posts_per_page'] : 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// Sort and Filter
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'latest';
$category_filter = isset($_GET['category_filter']) ? $_GET['category_filter'] : '';
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';

// Build SQL query
$sql = "SELECT posts.id, posts.title, posts.content, posts.created_at, posts.created_by ,posts.status, categories.category_name 
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

// Add pagination to the SQL query
$sql .= " LIMIT $offset, $posts_per_page";

$result = mysqli_query($connection, $sql);

if (!$result) {
    die("Query Failed: " . mysqli_error($connection));
}

// Fetch total number of posts for pagination
$total_query = "SELECT COUNT(*) AS total 
                FROM posts 
                JOIN categories ON posts.category_id = categories.id ";
if (count($conditions) > 0) {
    $total_query .= 'WHERE ' . implode(' AND ', $conditions);
}

$total_result = mysqli_query($connection, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_posts = $total_row['total'];
$total_pages = ceil($total_posts / $posts_per_page);

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
    <style>
        <style>.pagination {
            display: flex;
            justify-content: center;
            padding: 20px 0;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #007bff;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }

        .pagination a:hover {
            background-color: #e9ecef;
            color: #0056b3;
        }
    </style>
    </style>
</head>

<body>

    <?php include_once 'features/toast.html'; ?>

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
                    <select name="posts_per_page" class="form-select">
                        <option value="10" <?php if ($posts_per_page == 10)
                            echo 'selected'; ?>>10 per page</option>
                        <option value="25" <?php if ($posts_per_page == 25)
                            echo 'selected'; ?>>25 per page</option>
                        <option value="50" <?php if ($posts_per_page == 50)
                            echo 'selected'; ?>>50 per page</option>
                    </select>
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
                <?php if ($_SESSION['role'] === 'SuperAdmin'): ?>
                <th>Status</th>
                <?php endif; ?>
                <th>Created By</th>
                <th>Actions</th>
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
                    
                    <!-- Approve/Reject button -->
                    <?php if ($_SESSION['role'] === 'SuperAdmin'): ?>
                        <td>
                            <input type="checkbox" class="status-toggle" data-id="<?php echo $row['id']; ?>"
                            <?php if ($row['status'] == 'approved') echo 'checked'; ?> >
                            <span class="status-label"><?php echo $row['status'];?></span>

                            <script>
                                 document.addEventListener('DOMContentLoaded', function() {
                                 let statusToggles = document.querySelectorAll('.status-toggle');

                                 statusToggles.forEach(function(toggle) {
                                    //  toggle.removeEventListener('change', handleToggleChange);
                                     toggle.addEventListener('change', handleToggleChange);
                                 });
                             });    

                             function handleToggleChange() {
                                 let postId = this.getAttribute('data-id');
                                 let status = this.checked ? 'pending' : 'approved';
                                //  let statusLabel = document.querySelectorAll('.status-label');
                                //  let newStatus = this.checked ? 'approved' : 'pending';
                                //  console.log(postId, status);


                                 fetch('utils/toggle_post_status.php', {
                                     method: 'POST',
                                     headers: {
                                         'Content-Type': 'application/x-www-form-urlencoded'
                                     },
                                     body: `post_id=${postId}&status=${status}`
                                 })
                                 .then(response => response.json())
                                 .then(data => {
                                     console.log('Success:', data);
                                     if (data.success) {
                                         window.location.reload();
                                     }
                                 })
                                 .catch(error => console.error('Error:', error));
                             }

</script>
</td>
                    <?php endif; ?>
                    <td>
                        <?php echo htmlspecialchars($row['created_by']); ?>
                    </td>

                    <td>
                        <a href="EditPost.php?id=<?php echo $row['id']; ?>" class="btn btn-primary w-75">Edit</a>
                        <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'SuperAdmin'): ?>
                            <a href="DeletePost.php?id=<?php echo $row['id']; ?>"
                                onclick="return confirm('Are you sure you want to delete this post?');" class="btn btn-danger mt-2 w-75">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Pagination Links -->
        <div class="pagination d-flex justify-content-center">
            <?php if ($page > 1): ?>
                <a
                    href="?page=<?php echo $page - 1; ?>&sort_order=<?php echo urlencode($sort_order); ?>&category_filter=<?php echo urlencode($category_filter); ?>&search_query=<?php echo urlencode($search_query); ?>&posts_per_page=<?php echo $posts_per_page; ?>">&laquo;
                    Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&sort_order=<?php echo urlencode($sort_order); ?>&category_filter=<?php echo urlencode($category_filter); ?>&search_query=<?php echo urlencode($search_query); ?>&posts_per_page=<?php echo $posts_per_page; ?>"
                    class="<?php if ($i == $page)
                        echo 'active'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a
                    href="?page=<?php echo $page + 1; ?>&sort_order=<?php echo urlencode($sort_order); ?>&category_filter=<?php echo urlencode($category_filter); ?>&search_query=<?php echo urlencode($search_query); ?>&posts_per_page=<?php echo $posts_per_page; ?>">Next
                    &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>

</html>

<?php
mysqli_close($connection);
?>