<?php
session_start();
require 'db.php';

// Retrieve the search term from the form input (POST method)
$search_term = isset($_POST['q']) ? mysqli_real_escape_string($connection, $_POST['q']) : '';
$search_results = [];

if ($search_term) {
    // Update the query to search in the title field only
    $query = "SELECT id, title, category_id, subcategory_id FROM posts WHERE title LIKE ?";
    $stmt = mysqli_prepare($connection, $query);
    $like_term = '%' . $search_term . '%';
    mysqli_stmt_bind_param($stmt, "s", $like_term);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $search_results = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="common.css">
    <style>
        .navbar {
            background-color: #004d40;
        }

        .nav-link {
            color: white !important;
        }

        .sidebar {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .sidebar h3 {
            color: #d32f2f;
        }

        .main-content {
            padding: 20px;
        }

        .prev-next-buttons {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }

        .btn-nav {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
        }

        .btn-nav:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>

    <?php include_once 'safeheader.php'; ?>

    <div class="container-fluid">
        <div class="ro">
            <nav class="col-md-12 col-lg-12 border mx-auto sidebar">
                <h3>Search Results for "<?php echo htmlspecialchars($search_term); ?>"</h3>
                <ul class="nav flex-column">
                    <?php if ($search_results): ?>
                        <?php foreach ($search_results as $result): ?>
                            <li class="nav-item">
                                <i class="fa-solid fa-check mx-2" style="color:blue"></i>
                                <a
                                    href="post.php?category_id=<?php echo $result['category_id']; ?>&subcategory_id=<?php echo $result['subcategory_id']; ?>&post_id=<?php echo $result['id']; ?>">
                                    <?php echo htmlspecialchars($result['title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No results found for "<?php echo htmlspecialchars($search_term); ?>"</li>
                    <?php endif; ?>
                </ul>
            </nav>

            <main class="col-md- col-lg-10 main-content">
                <!-- Main content can be added here if needed -->
            </main>
        </div>
    </div>

    <footer class="bg-dark text-white py-3">
        <div class="container">
            <div class="d-flex justify-content-between">
                <p>&copy; 2023 Your Company. All rights reserved.</p>
                <div>
                    <a href="#" class="text-white mx-2">Privacy Policy</a>
                    <a href="#" class="text-white mx-2">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>