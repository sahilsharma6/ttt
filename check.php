<?php
session_start();
require 'db.php';

// Fetch all categories
$all_categories_query = "SELECT id, category_name FROM categories";
$all_categories_result = mysqli_query($connection, $all_categories_query);
$categories = mysqli_fetch_all($all_categories_result, MYSQLI_ASSOC);

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar {
            background-color: #004d40;
        }

        .navbar-brand,
        .nav-link {
            color: white !important;
        }

        .disclaimer {
            background-color: #c8e6c9;
            padding: 20px;
            margin-top: 20px;
        }

        .latest-tutorials img {
            width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <?php include_once 'header.php'; ?>


    <div class="container">
        <div class="disclaimer">
            <p><strong>Disclaimer:</strong> This website has no relation with 'Java', it is a free study portal where
                you can study 200+ technologies. It is an independent online platform created for educational and
                informational purposes.</p>
        </div>

        <div class="row my-4">
            <div class="col-md-8">
                <h3>Latest Tutorials</h3>
                <div class="row latest-tutorials">
                    <div class="col-6 col-md-4 mb-3">
                        <img src="https://via.placeholder.com/150" alt="Splunk">
                        <p>Splunk</p>
                    </div>
                    <!-- Add more tutorial blocks as needed -->
                </div>
            </div>

            <div class="col-md-4">
                <h3>Feedback</h3>
                <p>Send your feedback to <a href="mailto:feedback@example.com">feedback@example.com</a></p>
                <h3>Latest Updates</h3>
                <ul class="latest-updates">
                    <!-- Latest updates list -->
                </ul>
            </div>
        </div>
    </div>

    <?php include_once 'footer.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>