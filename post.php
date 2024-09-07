<?php
session_start();
require 'db.php';

// Get category ID, subcategory ID, and post ID from URL
$category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : null;
$subcategory_id = isset($_GET['subcategory_id']) ? (int) $_GET['subcategory_id'] : null;
// $current_post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0; 


// Fetch categories from the database
$categories = [];
$category_query = "SELECT id, category_name FROM categories";
$category_result = mysqli_query($connection, $category_query);
if ($category_result) {
    $categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
}

// Fetch subcategories and posts for the selected category
$subcategories = [];
if ($category_id) {
    // Fetch subcategories
    $subcategories_query = "SELECT id, subcategory_name FROM subcategories WHERE category_id = ? ";
    $stmt = mysqli_prepare($connection, $subcategories_query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $subcategories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // If no subcategory is selected, select the first subcategory by default
    if (!$subcategory_id && !empty($subcategories)) {
        $subcategory_id = $subcategories[0]['id'];
    }
}

// Fetch posts for the selected subcategory
$posts = [];
if ($subcategory_id) {
    $posts_query = "SELECT id, title FROM posts WHERE subcategory_id = ? AND status='approved'";
    $stmt = mysqli_prepare($connection, $posts_query);
    mysqli_stmt_bind_param($stmt, "i", $subcategory_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    if (!$post_id && count($posts) > 0) {
        $post_id = $posts[0]['id']; // Default to the first post if none is selected
        header("Location: post.php?category_id=$category_id&subcategory_id=$subcategory_id&post_id=$post_id");
        exit;
    }
}

// Fetch the selected post
$current_post = [];
if ($post_id) {
    $update_views_query = "UPDATE posts SET views = views + 1 WHERE id = ? AND status='approved'";
    $stmt = mysqli_prepare($connection, $update_views_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $post_query = "SELECT id, title, content, views,tags,likes,created_at,status FROM posts WHERE id = ? AND status='approved'";
    $stmt = mysqli_prepare($connection, $post_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $current_post = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Set default values if no post is found
    if (!$current_post) {
        $current_post = ['title' => 'No post found', 'content' => '', 'views' => 0];
    }

    // echo $current_post['status'] == "approved" ? "approved" : "pending";

    // Fetch comments
    // $limit = 5;
    // $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    // $offset = ($page - 1) * $limit;

    // // Fetch comments with pagination
    // $comments_query = "SELECT comments.id, comments.comment, comments.created_at, testt.username, comments.user_id 
    //                    FROM comments 
    //                    JOIN testt ON comments.user_id = testt.id 
    //                    WHERE post_id = ? 
    //                    ORDER BY comments.created_at DESC 
    //                    LIMIT ? OFFSET ?";

    // $stmt = mysqli_prepare($connection, $comments_query);
    // mysqli_stmt_bind_param($stmt, "iii", $post_id, $limit, $offset);
    // mysqli_stmt_execute($stmt);
    // $result = mysqli_stmt_get_result($stmt);
    // $comments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    // mysqli_stmt_close($stmt);


    // // // Calculate total number of pages
    // $total_comments_query = "SELECT COUNT(*) as total FROM comments WHERE post_id = ?";
    // $stmt = mysqli_prepare($connection, $total_comments_query);
    // mysqli_stmt_bind_param($stmt, "i", $post_id);
    // mysqli_stmt_execute($stmt);
    // $result = mysqli_stmt_get_result($stmt);
    // $total_comments = mysqli_fetch_assoc($result)['total'];
    // mysqli_stmt_close($stmt);

    // // echo $total_comments;
    // $total_pages = ceil($total_comments / $limit);

    // $total_comments = "SELECT COUNT(*) as total FROM comments WHERE post_id = $post_id";
    // $result = mysqli_query($connection, $total_comments);
    // $total_comments = mysqli_fetch_assoc($result)['total'];
}

// Determine the position of the current post in the $posts array
$current_post_index = array_search($post_id, array_column($posts, 'id'));

// Determine previous and next post IDs if they exist
$prev_post_id = $current_post_index > 0 ? $posts[$current_post_index - 1]['id'] : null;
$next_post_id = $current_post_index !== false && $current_post_index < count($posts) - 1 ? $posts[$current_post_index + 1]['id'] : null;

// Fetch the previous subcategory's last post if on the first post of the current subcategory
$prev_subcategory_id = null;
$prev_subcategory_last_post_id = null;
if ($current_post_index === 0 && !empty($subcategories)) {
    $prev_subcategory_index = array_search($subcategory_id, array_column($subcategories, 'id')) - 1;
    if ($prev_subcategory_index >= 0) {
        $prev_subcategory_id = $subcategories[$prev_subcategory_index]['id'];
        $prev_posts_query = "SELECT id FROM posts WHERE subcategory_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = mysqli_prepare($connection, $prev_posts_query);
        mysqli_stmt_bind_param($stmt, "i", $prev_subcategory_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $prev_post = mysqli_fetch_assoc($result);
        $prev_subcategory_last_post_id = $prev_post['id'];
        mysqli_stmt_close($stmt);
    }
}

// Fetch the next subcategory's first post if on the last post of the current subcategory
$next_subcategory_id = null;
$next_subcategory_first_post_id = null;
if ($current_post_index === count($posts) - 1 && !empty($subcategories)) {
    $next_subcategory_index = array_search($subcategory_id, array_column($subcategories, 'id')) + 1;
    if ($next_subcategory_index < count($subcategories)) {
        $next_subcategory_id = $subcategories[$next_subcategory_index]['id'];
        $next_posts_query = "SELECT id FROM posts WHERE subcategory_id = ? ORDER BY id ASC LIMIT 1";
        $stmt = mysqli_prepare($connection, $next_posts_query);
        mysqli_stmt_bind_param($stmt, "i", $next_subcategory_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $next_post = mysqli_fetch_assoc($result);
        $next_subcategory_first_post_id = $next_post['id'] ?? null;
        mysqli_stmt_close($stmt);
    }
}




mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($current_post['title'] ?? 'Post'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.27.0/themes/prism.min.css" rel="stylesheet" />

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
            padding: 0px 20px;
        }

        a {
            text-decoration: none;
        }

        ul li a {
            color: black;
            display: inline-block;
            padding-top: 5px;
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

        /* Ensure you include Font Awesome */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

        .sidebar h3 {
            color: #ff6600;
            /* Color for the subcategory headings */
            font-weight: bold;
            margin-top: 8px;
            display: flex;
            align-items: center;
            font-size: 19px;
            margin-bottom: 0;
        }

        .sidebar h3 .fas {
            margin-right: 8px;
            /* Space between icon and text */
        }

        .sidebar ul.list-group {
            /* margin-bottom: 1px; */
        }

        .sidebar li.list-group-item {
            list-style-type: none;
            padding: .5px 10px;
            display: flex;
            align-items: center;
            border: 0;
            background-color: re;
            background-color: #f8f9fa;
            font-size: 17px;

        }

        .sidebar li.list-group-item .fas {
            margin-right: 8px;
            /* Space between icon and text */
        }

        .sidebar li.list-group-item a {
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            transform: translateX(-4px);
            /* padding-right    : 10px */
            /* align-items: center; */
        }

        .sidebar li.list-group-item a span {

            transform: translateX(2px);
            /* padding-right    : 10px */
            /* align-items: center; */
        }

        .sidebar li.list-group-item a:hover {
            text-decoration: underline;
            color: blue;
        }

        .sidebar li.list-group-item.active {
            /* background-color: #f8f9fa; */
            /* Background color for active items */
        }

        .sidebar li.list-group-item.active a {
            color: #007bff;
            /* Color for active item links */
            font-weight: bold;
        }

        .language-javascript {
            background-color: #c6cacd;
            ;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }

        .language-javascript code {
            color: #000;
        }

        .menu-btn {
            display: none;
            position: absolute;
            top: 10px;
            left: 15px;
            font-size: 20px;
            cursor: pointer;
            /* bottom: 0; */
            z-index: 99999999999;
            /* right: 100%; */
        }

        .menu-ham {
            /* display: none; */
        }

        .menu-close {
            display: none;
            margin-top: 5px;
        }

        .sidebar.active {
            margin-top: 15px;
            right: 0%;
            z-index: 9999;
        }

        @media screen and (max-width: 767.5px) {
            .sidebar {
                /* display: none; */
                position: absolute;
                top: 0;
                bottom: 0;
                z-index: 999;
                right: 100%;
                transition: right 0.3s ease-in-out;
            }


            .menu-btn {
                display: block;
            }
        }
    </style>


</head>

<body>

    <?php include 'safeheader.php'; ?>
    <div class="menu-btn">
        <i class="fas fa-bars menu-ham"></i>
        <i class="fas fa-close menu-close"></i>
    </div>



    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 sidebar">
                <?php foreach ($subcategories as $subcategory): ?>
                    <h3>
                        <i class="fas fa-book"></i> <!-- Example icon for subcategories -->
                        <?php echo htmlspecialchars($subcategory['subcategory_name']); ?>
                    </h3>
                    <ul class="list-group">
                        <?php
                        // Fetch posts for the current subcategory
                        $posts_query = "SELECT id, title FROM posts WHERE subcategory_id = ? AND status='approved'";
                        $stmt = mysqli_prepare($connection, $posts_query);
                        mysqli_stmt_bind_param($stmt, "i", $subcategory['id']);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
                        mysqli_stmt_close($stmt);


                        foreach ($posts as $post): ?>
                            <!-- If post status is approved then show -->
                            <li class="list-group-item <?php if ($post_id === $post['id'])
                                echo 'bg-dark border-none text-white'; ?>">
                                <a class="<?php if ($post_id === $post['id'])
                                    echo 'text-white'; ?>"
                                    href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory['id']; ?>&post_id=<?php echo $post['id']; ?>">
                                    <i class="fas fa-file-alt "></i> <!-- Example icon for posts -->
                                    <span class="">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                <?php endforeach; ?>
            </div>


            <script>
                const menuBtn = document.querySelector('.menu-btn');
                const sidebar = document.querySelector('.sidebar');
                const menuHam = document.querySelector('.menu-ham');
                const menuClose = document.querySelector('.menu-close');

                menuBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                    menuHam.classList.toggle('d-none');
                    menuClose.classList.toggle('d-block');
                    console.log(sidebar);
                    console.log(7);
                    // menuClose.classList.toggle('active');
                })
            </script>

            <div class="col-md-9 main-content">
                <?php if ($post_id): ?>

                    <style>
                        .share-container {
                            position: relative;
                            display: inline-block;
                        }

                        .share-btn {
                            background-color: #f8f9fa;
                            border: none;
                            border-radius: 50%;
                            padding: 8px;
                            cursor: pointer;
                        }

                        .share-options {
                            display: none;
                            position: absolute;
                            top: 100%;
                            right: 0;
                            background-color: #fff;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
                            z-index: 1;
                            min-width: 160px;
                            text-align: left;
                        }

                        .share-option {
                            color: #333;
                            padding: 12px 16px;
                            text-decoration: none;
                            display: block;
                        }

                        .share-option:hover {
                            background-color: #f1f1f1;
                        }

                        .show-options {
                            display: block;
                        }
                    </style>
                    <style>
                        .share-container {
                            position: relative;
                            display: inline-block;
                        }

                        .share-btn {
                            /* background-color: #f8f9fa; */
                            background: none;
                            /* border: none; */
                            /* width: 50px; */
                            /* border-radius: 50%; */
                            /* padding: 8px; */
                            /* display: inline; */
                            cursor: pointer;
                            font-size: 1.2rem;
                            transition: background-color 0.3s, transform 0.3s;
                        }

                        .share-btn:hover {
                            /* background-color: #e9ecef; */
                            transform: scale(1.1);
                        }

                        .share-options {
                            display: none;
                            position: absolute;
                            top: 100%;
                            right: 0;
                            background-color: #fff;
                            border: 1px solid #ddd;
                            border-radius: 40px;
                            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
                            z-index: 1;
                            min-width: 160px;
                            text-align: left;
                            opacity: 0;
                            visibility: hidden;
                            transition: opacity 0.3s, visibility 0.3s;
                        }

                        .share-options.show-options {
                            display: block;
                            opacity: 1;
                            visibility: visible;
                        }

                        .share-option {
                            color: #333;
                            padding: 12px 16px;
                            text-decoration: none;
                            display: block;
                            transition: background-color 0.3s, color 0.3s;
                        }

                        .share-option:hover {
                            background-color: #f1f1f1;
                            color: #007bff;
                        }

                        .copy {
                            cursor: pointer;
                        }
                    </style>


                    <!-- Three-dot button -->
                    <div class="d-flex align-items-center justify-content-between  ">
                        <div>
                            <?php
                            $date = new DateTime($current_post['created_at']);
                            // echo $date->format('d M Y'); // Outputs: 24 Aug 2024
                            ?>
                        </div>
                        <div class="share-container">
                            <button class="share-btn">
                                <!-- <i class="fas fa-ellipsis-v"></i> -->
                                <!-- <span>
                                    <i class="fa-regular fa-share-from-square"></i>
                                </span> -->
                                <span class="mx-3"><i class="fa-regular fa-share-from-square"></i> Share</span>


                            </button>
                            <!-- Share options -->
                            <div class="share-options">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://yourwebsite.com/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>"
                                    target="_blank" class="share-option">
                                    <i class="fab fa-facebook-f"></i> Share on Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://yourwebsite.com/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>&text=<?php echo urlencode($current_post['title']); ?>"
                                    target="_blank" class="share-option">
                                    <i class="fab fa-twitter"></i> Share on Twitter
                                </a>
                                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode('https://yourwebsite.com/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>"
                                    target="_blank" class="share-option">
                                    <i class="fab fa-whatsapp"></i> Share on WhatsApp
                                </a>
                                <span class="share-option copy">
                                    <i class="fa fa-link"></i> Copy Link
                                    <input type="text" hidden
                                        value="https://yourwebsite.com/post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory_id; ?>&post_id=<?php echo $post_id; ?>"
                                        id="copyLinkInput" readonly>
                                </span>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.querySelector('.share-btn').addEventListener('click', function () {
                            const shareOptions = document.querySelector('.share-options');
                            shareOptions.classList.toggle('show-options');
                        });

                        // window.addEventListener('click', function (event) {
                        //     if (!event.target.matches('.share-btn')) {
                        //         const shareOptions = document.querySelector('.share-options');
                        //         if (shareOptions.classList.contains('show-options')) {
                        //             shareOptions.classList.remove('show-options');
                        //         }
                        //     }
                        // });

                        document.querySelector('.copy').addEventListener('click', function () {
                            const copyLinkInput = document.getElementById('copyLinkInput');
                            let copyText = copyLinkInput.value;

                            navigator.clipboard.writeText(copyText).then(function () {
                                showToast('success', 'Link copied to clipboard!');
                                // alert('Link copied to clipboard!');
                            }, function (err) {
                                console.error('Could not copy text: ', err);
                            });
                        });
                    </script>
                    <!--  -->


                    <h2 class="mt-"><?php echo htmlspecialchars($current_post['title']); ?></h2>
                    <div class="content">
                        <?php echo (($current_post['content'])); ?>
                    </div>


                    <div class="prev-next-buttons">
                        <?php if ($current_post_index === 0 && $prev_subcategory_last_post_id): ?>
                            <a class="btn-nav"
                                href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $prev_subcategory_id; ?>&post_id=<?php echo $prev_subcategory_last_post_id; ?>">
                                <!-- <i class="fa-solid fa-arrow-left mx-1"></i> -->
                                ❮ Previous</a>
                        <?php elseif ($prev_post_id): ?>
                            <a class="btn-nav"
                                href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory_id; ?>&post_id=<?php echo $prev_post_id; ?>">
                                <!-- <i class="fa-solid fa-arrow-left mx-1"></i> -->
                                ❮ Previous </a>
                        <?php endif; ?>

                        <?php if ($next_post_id): ?>
                            <a class="btn-nav"
                                href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory_id; ?>&post_id=<?php echo $next_post_id; ?>">Next
                                ❯
                                <!-- <i class="fa-solid fa-arrow-right mx-1"></i> -->
                            </a>
                        <?php elseif ($next_subcategory_first_post_id): ?>
                            <a class="btn-nav"
                                href="post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $next_subcategory_id; ?>&post_id=<?php echo $next_subcategory_first_post_id; ?>">Next
                                ❯
                                <!-- <i class="fa-solid fa-arrow-right mx-1"></i> -->
                            </a>
                        <?php endif; ?>
                    </div>


                    <!-- ------------------likes------------------ -->
                    <div class="d-flex justify-content-between align-items-center border-top border-bottom ">

                        <div class="like" role="button">
                            <span id="like-count"> </span>
                            <!-- <i class="fa-regular fa-thumbs-up" id="like-button"></i> -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                id="like-button" aria-label="clap">
                                <path fill-rule="evenodd"
                                    d="M11.37.828 12 3.282l.63-2.454zM13.916 3.953l1.523-2.112-1.184-.39zM8.589 1.84l1.522 2.112-.337-2.501zM18.523 18.92c-.86.86-1.75 1.246-2.62 1.33a6 6 0 0 0 .407-.372c2.388-2.389 2.86-4.951 1.399-7.623l-.912-1.603-.79-1.672c-.26-.56-.194-.98.203-1.288a.7.7 0 0 1 .546-.132c.283.046.546.231.728.5l2.363 4.157c.976 1.624 1.141 4.237-1.324 6.702m-10.999-.438L3.37 14.328a.828.828 0 0 1 .585-1.408.83.83 0 0 1 .585.242l2.158 2.157a.365.365 0 0 0 .516-.516l-2.157-2.158-1.449-1.449a.826.826 0 0 1 1.167-1.17l3.438 3.44a.363.363 0 0 0 .516 0 .364.364 0 0 0 0-.516L5.293 9.513l-.97-.97a.826.826 0 0 1 0-1.166.84.84 0 0 1 1.167 0l.97.968 3.437 3.436a.36.36 0 0 0 .517 0 .366.366 0 0 0 0-.516L6.977 7.83a.82.82 0 0 1-.241-.584.82.82 0 0 1 .824-.826c.219 0 .43.087.584.242l5.787 5.787a.366.366 0 0 0 .587-.415l-1.117-2.363c-.26-.56-.194-.98.204-1.289a.7.7 0 0 1 .546-.132c.283.046.545.232.727.501l2.193 3.86c1.302 2.38.883 4.59-1.277 6.75-1.156 1.156-2.602 1.627-4.19 1.367-1.418-.236-2.866-1.033-4.079-2.246M10.75 5.971l2.12 2.12c-.41.502-.465 1.17-.128 1.89l.22.465-3.523-3.523a.8.8 0 0 1-.097-.368c0-.22.086-.428.241-.584a.847.847 0 0 1 1.167 0m7.355 1.705c-.31-.461-.746-.758-1.23-.837a1.44 1.44 0 0 0-1.11.275c-.312.24-.505.543-.59.881a1.74 1.74 0 0 0-.906-.465 1.47 1.47 0 0 0-.82.106l-2.182-2.182a1.56 1.56 0 0 0-2.2 0 1.54 1.54 0 0 0-.396.701 1.56 1.56 0 0 0-2.21-.01 1.55 1.55 0 0 0-.416.753c-.624-.624-1.649-.624-2.237-.037a1.557 1.557 0 0 0 0 2.2c-.239.1-.501.238-.715.453a1.56 1.56 0 0 0 0 2.2l.516.515a1.556 1.556 0 0 0-.753 2.615L7.01 19c1.32 1.319 2.909 2.189 4.475 2.449q.482.08.971.08c.85 0 1.653-.198 2.393-.579.231.033.46.054.686.054 1.266 0 2.457-.52 3.505-1.567 2.763-2.763 2.552-5.734 1.439-7.586z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Like(s)</span>
                            <span class="mx-3"><i class="fa-regular fa-share-from-square"></i> Share</span>

                        </div>
                        <div class="views mt-3">
                            <p> <?php echo $current_post['views']; ?> View(s)</p>
                        </div>

                    </div>




                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const likeButton = document.getElementById('like-button');
                            const likeCount = document.getElementById('like-count');
                            let postId = <?php echo json_encode($post_id); ?>;


                            function fetchLikeCount() {

                                fetch('utils/fetch_like_count.php?post_id=' + postId + '', {
                                    method: 'GET',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        likeCount.textContent = ` ${data.like_count} `;
                                        console.log(data);
                                    })
                                    .catch(error => console.error('Error:', error));
                            }

                            likeButton.addEventListener('click', function () {
                                fetch('utils/like_post.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `post_id=${postId}`
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            // likeButton.classList.toggle('fa-solid');
                                            showToast("success", "Post liked");
                                            fetchLikeCount();
                                        } else {
                                            fetchLikeCount();
                                            // showToast("error", "Please login to like");
                                            <?php if (!isset($_SESSION['user_id'])) { ?>
                                                showToast("error", "Please signup or login to like");

                                            <?php } else {
                                                ?>

                                                showToast("error", "Post unliked");

                                            <?php }
                                            ; ?>


                                            console.error('Error liking/unliking post');
                                        }

                                    })
                                    .catch(error => console.error('Error:', error));
                            });

                            fetchLikeCount();
                        });
                    </script>

                    <?php include 'features/postTags.php'; ?>


                    <!-- ------------------------------------- -->


                    <!-- share -->
                    <!-- <div class="share-buttons">
                        <h4>Share this post:</h4>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://yourwebsite.com/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>"
                            target="_blank" class="btn btn-primary">
                            <i class="fab fa-facebook-f"></i> Share on Facebook
                        </a>

                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://yourwebsite.com/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>&text=<?php echo urlencode($current_post['title']); ?>"
                            target="_blank" class="btn btn-info">
                            <i class="fab fa-twitter"></i> Share on Twitter
                        </a>

                        <a href="http"></a>

                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode('localhost/tutorial-test/My/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>"
                            target="_blank" class="btn btn-success">
                            <i class="fab fa-whatsapp"></i> Share on WhatsApp
                        </a>
                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode('localhost/tutorial-test/My/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>"
                            target="_blank" class="btn btn-success">
                            <i class="fab fa-whatsapp"></i> Share on WhatsApp
                        </a>

                    </div> -->


                    <!-- Three-dot button -->





                    <div class="modal fade" id="editCommentModal" tabindex="-1" aria-labelledby="editCommentModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editCommentModalLabel">Edit Comment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="editCommentForm">
                                        <input type="hidden" name="comment_id" id="comment_id">
                                        <div class="mb-3">
                                            <label for="comment_text" class="form-label">Comment</label>
                                            <textarea class="form-control" id="comment_text" name="comment_text"
                                                rows="3"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php include "features/toast.html"; ?>

                    <form id="comment-form">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <h3 class="mt-4 mb-3">Add Comment</h3>

                            <input type="hidden" name="post_id" id="post-id" value="<?php echo $post_id; ?>">
                            <!-- Replace with actual post ID -->
                            <div class="form-group">
                                <textarea id="comment-text" name="comment" class="form-control" rows="3"
                                    placeholder="Leave a comment..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Post Comment</button>

                        <?php else: ?>
                            <p class="mt-4">
                                please <a href="login.php">login</a> to post a comment
                            </p>

                        <?php endif; ?>
                    </form>



                    <script>
                        console.log(<?php echo json_encode($_SESSION['user_id']); ?>);
                    </script>


                    <!-- Comments -->
                    <div class="comments">
                        <h3 class="comment-count mt-5">Comments</h3>
                        <div id="comments-list"></div>
                        <div id="pagination"></div>
                    </div>
                    <script>
                        const postId = <?php echo json_encode($post_id); ?>;
                        const commentsList = document.getElementById('comments-list');
                        const pagination = document.getElementById('pagination');
                        let commentCount = document.querySelector('.comment-count')
                        let currentPage = 1;


                        // Fetch comments on page load
                        function fetchComments(page) {
                            fetch(`utils/fetch_comments.php?post_id=${postId}&page=${page}`)
                                .then(response => response.json())
                                .then(data => {
                                    commentCount.textContent = `Comments (${data.total_comments})`;

                                    // Clear existing comments
                                    commentsList.innerHTML = '';

                                    // Display comments
                                    data.comments.forEach(comment => {
                                        const commentElement = document.createElement('li');
                                        commentElement.className = 'list-group-item';
                                        commentElement.setAttribute('data-comment-id', comment.id);
                                        console.log(comment);
                                        commentElement.innerHTML = `
                                                                                                                                                                                           <strong class="mx-1  ">${comment.username}</strong>
                                                                                                                                                                                           <small>${new Date(comment.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</small>


                                                                                                                                                                                           <p>${comment.comment}</p>
                                                                                                                                                                                           `;


                                        <?php
                                        // $user_id = $_SESSION['role'];
                                        // echo $user_id;
                                    
                                        ?>
                                        // Only show edit and delete buttons if the user is logged in and is the owner of the comment
                                        <?php if (isset($_SESSION['username'])): ?>
                                            if (comment.username === '<?php echo $_SESSION['username']; ?>') {

                                                commentElement.innerHTML += `
                                                                                                                                                                                                                                                                                                                                        <a href="#" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" 
                                                                                                                                                                                                                                                                                                                                            data-bs-target="#editCommentModal" 
                                                                                                                                                                                                                                                                                                                                          data-comment-id="${comment.id}" 
                                                                                                                                                                                                                                                                                                                                          data-comment-text="${comment.comment}">Edit</a>
                                                                                                                                                                                                                                                                                                                          <a href="#" class="btn btn-sm btn-outline-danger" 
                                                                                                                                                                                                                                                                                                                                         data-comment-id="${comment.id}">Delete</a>`;
                                            }

                                        <?php endif; ?>

                                        commentsList.appendChild(commentElement);
                                    });

                                    // Setup pagination
                                    pagination.innerHTML = '';
                                    const ul = document.createElement('ul');
                                    ul.className = 'pagination mt-3';

                                    // Previous Page
                                    if (page > 1) {
                                        const prevItem = document.createElement('li');
                                        prevItem.className = 'page-item';
                                        const prevLink = document.createElement('a');
                                        prevLink.className = 'page-link';
                                        prevLink.href = '#';
                                        prevLink.setAttribute('aria-label', 'Previous');
                                        prevLink.innerHTML = `<span aria-hidden="true">&laquo;</span>`;
                                        prevLink.addEventListener('click', (e) => {
                                            e.preventDefault();
                                            fetchComments(page - 1);
                                        });
                                        prevItem.appendChild(prevLink);
                                        ul.appendChild(prevItem);
                                    }

                                    // Page Numbers
                                    for (let i = 1; i <= data.total_pages; i++) {
                                        const pageItem = document.createElement('li');
                                        pageItem.className = `page-item ${i === page ? 'active' : ''}`;
                                        const pageLink = document.createElement('a');
                                        pageLink.className = 'page-link';
                                        pageLink.href = '#';
                                        pageLink.textContent = i;
                                        pageLink.addEventListener('click', (e) => {
                                            e.preventDefault();
                                            fetchComments(i);
                                        });
                                        pageItem.appendChild(pageLink);
                                        ul.appendChild(pageItem);
                                    }

                                    // Next Page
                                    if (page < data.total_pages) {
                                        const nextItem = document.createElement('li');
                                        nextItem.className = 'page-item';
                                        const nextLink = document.createElement('a');
                                        nextLink.className = 'page-link';
                                        nextLink.href = '#';
                                        nextLink.setAttribute('aria-label', 'Next');
                                        nextLink.innerHTML = `<span aria-hidden="true">&raquo;</span>`;
                                        nextLink.addEventListener('click', (e) => {
                                            e.preventDefault();
                                            fetchComments(page + 1);
                                        });
                                        nextItem.appendChild(nextLink);
                                        ul.appendChild(nextItem);
                                    }

                                    pagination.appendChild(ul);
                                })
                                .catch(error => console.error('Error fetching comments:', error));
                        }

                        // Add event listener to comment form
                        document.getElementById('comment-form').addEventListener('submit', function (event) {
                            event.preventDefault();

                            var formData = new FormData(this);

                            fetch('utils/process_comment.php', {
                                method: 'POST',
                                body: formData
                            }).then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        showToast('success', 'Comment added successfully!');

                                        fetchComments(currentPage);
                                        document.getElementById('comment-text').value = '';
                                    } else {
                                        showToast('error', 'Comment can not be empty!');
                                    }
                                })
                                .catch(error => console.error('Error:', error));
                        });

                        // Edit comment
                        var editCommentModal = document.getElementById('editCommentModal');

                        editCommentModal.addEventListener('show.bs.modal', function (event) {
                            var button = event.relatedTarget;
                            var commentId = button.getAttribute('data-comment-id');
                            var commentText = button.getAttribute('data-comment-text');

                            var modalCommentId = editCommentModal.querySelector('#comment_id');
                            var modalCommentText = editCommentModal.querySelector('#comment_text');

                            modalCommentId.value = commentId;
                            modalCommentText.value = commentText;
                        });

                        document.getElementById('editCommentForm').addEventListener('submit', function (e) {
                            e.preventDefault();
                            var formData = new FormData(this);

                            fetch('utils/edit_comment.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        showToast('success', 'Comment updated successfully!');
                                        fetchComments(currentPage);
                                        bootstrap.Modal.getInstance(editCommentModal).hide();
                                    } else {
                                        alert('Error updating comment: ' + data.message);
                                    }
                                })
                                .catch(error => console.error('Error:', error));
                        });

                        // Delete comment
                        function deleteComment(commentId) {
                            event.preventDefault();
                            if (confirm('Are you sure you want to delete this comment?')) {
                                fetch('utils/delete_comment.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `id=${commentId}`
                                })
                                    .then(response => response.text())
                                    .then(result => {
                                        if (result.trim() === "success") {
                                            showToast('success', 'Comment deleted successfully!');
                                            fetchComments(currentPage);
                                        } else {
                                            alert("Error deleting comment: " + result);
                                        }
                                    })
                                    .catch(error => console.error('Error deleting comment:', error));
                            }
                        }

                        // Event delegation for dynamically added delete buttons
                        commentsList.addEventListener('click', function (event) {
                            if (event.target && event.target.matches('a.btn-outline-danger')) {
                                const commentId = event.target.getAttribute('data-comment-id');
                                deleteComment(commentId);
                            }
                        });

                        fetchComments(currentPage); // Initial load
                    </script>

                </div>
            <?php else: ?>
                <p>No post found.</p>
            <?php endif; ?>

        </div>
    </div>

    <?php include 'features/scrollToTopBtn.html'; ?>

    <?php require_once 'footer.php'; ?>


    <script>
        function copyToClipboardCode() {
            const codeElements = document.querySelectorAll('.language-javascript');

            codeElements.forEach((codeElement) => {
                const copyBtn = document.createElement('button');
                const div = document.createElement('div');

                div.style.position = 'absolute';
                div.style.right = '30px';
                copyBtn.style.border = 'none';
                copyBtn.style.backgroundColor = 'transparent';

                copyBtn.innerHTML = '<i class="fa-regular fa-copy"></i>';

                // Append the button to the div
                div.appendChild(copyBtn);

                // Insert the div before the code element
                codeElement.parentNode.insertBefore(div, codeElement);

                // Add event listener to the copy button
                copyBtn.addEventListener('click', () => {
                    navigator.clipboard.writeText(codeElement.textContent)
                        .then(() => {
                            copyBtn.textContent = 'Copied!';
                            showToast('success', 'Copied to clipboard!');
                            setTimeout(() => {
                                copyBtn.innerHTML = '<i class="fa-regular fa-copy"></i>';
                            }, 2000);
                        })
                        .catch(err => {
                            console.error('Failed to copy text: ', err);
                        });
                });
            });
        }

        // Call the function to apply the changes
        copyToClipboardCode();
    </script>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.27.0/prism.min.js"></script>

</body>

</html>