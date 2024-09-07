<?php

// session_start();
require 'db.php';


$role = $_SESSION['role'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$category_id = $_GET['category_id'] ?? null;
// echo $category_id;
// echo $user_id;
// Fetch categories from the database
$categories = [];
$category_query = "SELECT id, category_name, category_image FROM categories";
$category_result = mysqli_query($connection, $category_query);
if ($category_result) {
    $categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
} else {
    echo "Error fetching categories: " . mysqli_error($connection);
}

// Initialize variables for navlinks
$navlinks = [];
$current_category = null;

// Check if a category_id is provided
if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);

    // Fetch the navlinks for the selected category
    $stmt = $connection->prepare("SELECT * FROM navlinks WHERE category_id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $navlinks = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch the current category details
    $stmt = $connection->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $category_result = $stmt->get_result();
    $current_category = $category_result->fetch_assoc();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['username']);
$username = $is_logged_in ? htmlspecialchars($_SESSION['username']) : '';

?>




<header class="bg-light py-3 position- header " style="position: ">
    <div class="container-fluid d-flex justify-content- align-items-center">
        <a class="navbar-brand" href="./check.php">
            <img src="uploads/logo.png" height="80" alt="logo">
        </a>
        <form class="d-flex" method="POST" action="search.php" role="search" class="search-form">
            <!-- <input class="form-control me-2" type="search" name="q" placeholder="Search" aria-label="Search" /> -->
            <div class="d-none search d-md-flex  p-2 ">
                <input class="form-contro w-100 p-2 border " type="search" name="q"
                    placeholder="Search tutorials, courses and ebooks..." aria-label="Search" />
                <button class="btn btn-outline-dark " type="submit" style="border-radius: 0px 5px 5px 0px; ;">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <!-- <button class="btn btn-outline-success" type="submit">Search</button> -->
        </form>



        <div class="position-absolute login  d-flex align-items-center justify-content-center d-one "
            style="cursor:pointer; ">
            <div>
                <div id="google_translate_element" class="" style="
                margin-left: 200px;
                display:none;
                transition: 1s ease-in-out;
                "></div>

                <script type="text/javascript">
                    function googleTranslateElementInit() {
                        new google.translate.TranslateElement({ pageLanguage: 'en' }, 'google_translate_element');
                    }
                </script>

                <script type="text/javascript"
                    src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
            </div>
            <div class="mx-4">
                <!-- <i class="fa-solid fa-globe"></i> -->
                <i class="fa-solid fa-language " style="font-size:25px" title="Change Language"></i>
            </div>
            <span><i class="gfg-icon  gfg-icon-black-translate "></i></span>

            <script>
                const faGlobe = document.querySelector('.fa-language');
                faGlobe.addEventListener('click', () => {
                    const googleTranslateElement = document.querySelector('#google_translate_element');
                    googleTranslateElement.style.display = googleTranslateElement.style.display === 'none' ? 'block' : 'none';
                })
            </script>



            <style>
                .show-username {
                    display: none;
                    top: 48px;
                    right: -17px;
                    padding: 10px;
                    border-radius: 10px;
                    z-index: 99999999999;
                    color: black;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
                }
            </style>

            <div class="pt- cursor-pointer login-btn d-flex  justify-content-center flex-column  login-btn  "
                style="justify-content: center;">

                <i class="fa-solid fa-user text-center  "></i>
                <?php if ($is_logged_in): ?>
                    <!-- <span> <?php echo $username; ?></span> -->
                    <p class="login-username " style=""> <?php echo $username; ?> </p>

                    <div class="d- show-username bg-light text-bg-  position-absolute">
                        <div class="my-2">
                            <i class="fa-regular fa-user"></i>
                            <span class="show-username-text">

                                <?php echo $username; ?>
                            </span>
                        </div>


                        <?php if ($role === 'Admin' || $role === 'SuperAdmin' || $role === 'Operator'): ?>
                            <p class="" style="height:.1px; width:100%; background-color: #edebe6;"></p>
                            <div class="mb-3">
                                <a href="dashboard.php" style="color:black;">
                                    <!-- <i class="fa-regular fa-user"></i> -->
                                    <i class="fa-solid fa-house"></i>
                                    <span>
                                        Dashboard
                                    </span>
                                </a>
                            </div>
                        <?php endif; ?>
                        <!-- <hr> -->
                        <p class="" style="height:.1px; width:100%; background-color: #edebe6;"></p>
                        <div>
                            <a href="logout.php" class="text-" style="text-decoration: none; color:white;">
                                <p class="text-" style="color:blue;">
                                    <i class="fa-solid fa-right-from-bracket "></i>
                                    Sign out
                                </p>
                            </a>
                        </div>
                    </div>




                <?php else: ?>
                    <a href="login.php" style="text-decoration: none;">Sign in</a>
                <?php endif; ?>
            </div>
            <script>
                const loginUsername = document.querySelector('.login-username');
                const showUsername = document.querySelector('.show-username');
                const showUsernameTxt = document.querySelector('.show-username-text');
                const loginBtn = document.querySelector('.login-btn');
                // const username = loginUsername.textContent.chartAt(0).toUpperCase() + loginUsername.textextContent.slice(1);
                const usernameStr = loginUsername.textContent
                console.log(usernameStr);
                let username = usernameStr.trim()
                username = username.charAt(0).toUpperCase() + username.slice(1)
                console.log(username);



                loginUsername.textContent = username;
                showUsernameTxt.textContent = username;

                loginBtn.addEventListener('click', () => {
                    showUsername.style.display = showUsername.style.display === 'none' ? 'block' : 'none';
                })
                // loginUsername


                if (loginUsername.textContent.length > 10) {
                    loginUsername.textContent = loginUsername.textContent.slice(0, 10) + '...';
                }




                // console.log(loginUsername.textContent.length);
            </script>
        </div>
    </div>
</header>

<section class="d-md-none d-block">
    <div class="container-fluid border">
        <form class="d-flex" method="POST" action="search.php" role="search" class="w-100 ">
            <!-- <input class="form-control me-2" type="search" name="q" placeholder="Search" aria-label="Search" /> -->
            <div class="d-flex w-100   p-2 m-0 " style="background-color: ">
                <input class="form-contol w-100 px-1 border " style="font-size: 14px" type="search" name="q"
                    placeholder="Search tutorials, courses and ebooks..." aria-label="Serch" />
                <button class="btn btn-outline-dark " type="submit" style="border-radius: 0px 5px 5px 0px; ;">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <!-- <button class="btn btn-outline-success" type="submit">Search</button> -->
        </form>
    </div>
</section>


<?php if ($current_category !== null): ?>
    <nav class=" mynav">
        <!-- <h1><?php echo htmlspecialchars($current_category['category_name']); ?> Navigation Links</h1> -->
        <ul>
            <?php if (!empty($navlinks)): ?>
                <?php foreach ($navlinks as $navlink): ?>
                    <?php


                    // $navlink_id = substr($navlink['url'], -2);
        
                    $url = $navlink['url'];
                    $url_components = parse_url($url);
                    parse_str($url_components['query'], $params);
                    $navlink_id = $params['category_id'];
                    // $navlink_id = ;    
        
                    $stmt = $connection->prepare("SELECT * FROM categories WHERE id = ?");
                    $stmt->bind_param('i', $navlink_id);
                    $stmt->execute();
                    $category_result = $stmt->get_result();
                    $category = $category_result->fetch_assoc();

                    // echo '<li><img src="' . $category['category_image'] . '" alt=""></li>';
                    // $category_img = $category['category_image'];
                    // echo $navlink_id;
                    // echo $navlink['url'];
                    // echo $category['category_image'];
                    // foreach()
                    // if ($category !== null):
        
                    // $category_id = $category['id'];
        
                    // echo $navlink_id;
                    // echo $category['id'];
                    ?>


                    <a href="<?php echo htmlspecialchars($navlink['url']); ?>">

                        <li class="

                        <?php

                        if ($category['id'] == $current_category['id']) {
                            echo 'bg-dark';
                        } else {
                            // echo 'bg-light';
                        }
                        ?>
                        ">
                            <div class="">

                                <img src="<?php echo $category['category_image']; ?>" height="20" width="20" alt="">
                                <span class="na">
                                    <?php echo htmlspecialchars($navlink['name']); ?>
                                </span>
                            </div>
                        </li>
                    </a>





                <?php endforeach; ?>
            <?php else: ?>
                <!-- <li>No navigation links available for this category.</li> -->

                <nav class="mynav">
                    <ul>
                        <?php foreach ($categories as $category): ?>

                            <?php
                            ?>
                            <a href="post.php?category_id=<?php echo $category['id']; ?>">
                                <li class="
                                     <?php

                                     if ($category['id'] == $current_category['id']) {
                                         echo 'bg-dark';
                                     } else {
                                         // echo 'bg-light';
                                     }
                                     ?>
                                ">
                                    <div class="">

                                        <img src="<?php echo $category['category_image']; ?>" height="20" width="20">
                                        <span class="na">
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </span>
                                    </div>
                                </li>
                            </a>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </ul>
    </nav>


<?php endif; ?>

<!-- 
<nav class="mynav">
    <ul>
        <?php foreach ($categories as $category): ?>
            <a href="post.php?category_id=<?php echo $category['id']; ?>">
                <li class="">
                    <div class="">
                        <img src="<?php echo $category['category_image']; ?>" height="20" alt="">
                        <span class="na">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </span>
                    </div>
                </li>
            </a>
        <?php endforeach; ?>

    </ul>
</nav> -->

<script>
    window.onscroll = function () {
        myFunction();
    };

    // function myFunction() {
    //     if (
    //         document.body.scrollTop > 10 ||
    //         document.documentElement.scrollTop > 10
    //     ) {
    //         // document.querySelector(".navbar").classList.add("bg-black");
    //         document.querySelector(".mynav").classList.add("sticky-top");
    //         document.querySelector(".mynav").style.transition = "all 0.5s ease 10s";
    //     } else {
    //         document.querySelector(".mynav").classList.remove("sticky-top");
    //         // document.querySelector("header").classList.remove("fixed-lg-top");
    //         document.querySelector(".mynav").style.transition = "";

    //     }
    // }

    let lastScrollTop = 0;

    const header = document.querySelector(".header");
    // header.classList.add("b");
    console.log(header);
    function myFunction() {
        const currentScrollTop = window.scrollY;

        if (currentScrollTop > lastScrollTop) {
            console.log('d');
            document.querySelector(".mynav").classList.remove("sticky-top");

        } else {
            console.log('u');
            document.querySelector(".mynav").classList.add("sticky-top");

            document.querySelector(".mynav").style.transition = "all 5s ease-in-out";

        }
        lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;


    }
</script>