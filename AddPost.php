<?php
session_start();
require 'db.php';

// Check if the user is logged in and has the right role
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin' && $_SESSION['role'] !== 'Operator')) {
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
    if (isset($_POST['add_post'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $category_id = $_POST['category_id'];
        $subcategory_id = $_POST['subcategory_id'];
        $views = $_POST['views'];
        $created_by = $_SESSION['username'];

        if (!empty($title) && !empty($content) && !empty($category_id) && !empty($subcategory_id)) {
            // Prepare an insert statement
            $stmt = mysqli_prepare($connection, "INSERT INTO posts (title, content, category_id, subcategory_id, created_by, views) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssiisi", $title, $content, $category_id, $subcategory_id, $created_by, $views);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Post added successfully!";
            } else {
                $error = "Something went wrong. Please try again.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = "Please fill in all fields.";
        }
    } elseif (isset($_POST['fetch_subcategories'])) {
        $category_id = $_POST['category_id'];
        $subcategories = [];
        $result = mysqli_query($connection, "SELECT id, subcategory_name FROM subcategories WHERE category_id = $category_id");
        while ($row = mysqli_fetch_assoc($result)) {
            $subcategories[] = $row;
        }

        if (!empty($subcategories)) {
            foreach ($subcategories as $subcategory) {
                echo '<option value="' . $subcategory['id'] . '">' . htmlspecialchars($subcategory['subcategory_name']) . '</option>';
            }
        } else {
            echo '<option value="">No subcategories found</option>';
        }
        exit();
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php include_once 'sidebar.php'; ?>
    <div class="dash-content z">
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
                <textarea id="content" name="content"></textarea> <!-- Remove the required attribute here -->
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
            <div class="mb-3">
                <label for="subcategory_id" class="form-label">Subcategory</label>
                <select class="form-select" id="subcategory_id" name="subcategory_id" required>
                    <option value="">Select a subcategory</option>
                    <!-- Subcategories will be loaded here via AJAX -->
                </select>
            </div>
            <div class="mb-3">
                <label for="views" class="form-label">Views</label>
                <input type="number" class="form-control" id="views" name="views" required>
            </div>
            <button type="submit" class="btn btn-primary" name="add_post">Add Post</button>
        </form>
        <?php echo $_SESSION['username']; ?>
    </div>

    <!-- Include TinyMCE script -->
    <script src="https://cdn.tiny.cloud/1/lfqevskjzwe9ooap19ndn8lbigt79ghkothcuuyb704olerc/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: "#content",
            plugins:
                "preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons accordion",
            editimage_cors_hosts: ["picsum.photos"],
            menubar: "file edit view insert format tools table help",
            toolbar:
                "undo redo | accordion accordionremove | blocks fontfamily fontsize | bold italic underline strikethrough | align numlist bullist | link image | table media | lineheight outdent indent | forecolor backcolor removeformat | charmap emoticons | code fullscreen preview | save print | pagebreak anchor codesample | ltr rtl",
            autosave_ask_before_unload: true,
            autosave_interval: "30s",
            autosave_prefix: "{path}{query}-{id}-",
            autosave_restore_when_empty: false,
            autosave_retention: "2m",
            image_advtab: true,
            link_list: [
                { title: "My page 1", value: "https://www.tiny.cloud" },
                { title: "My page 2", value: "http://www.moxiecode.com" },
            ],
            image_list: [
                { title: "My page 1", value: "https://www.tiny.cloud" },
                { title: "My page 2", value: "http://www.moxiecode.com" },
            ],
            image_class_list: [
                { title: "None", value: "" },
                { title: "Some class", value: "class-name" },
            ],
            importcss_append: true,
            file_picker_callback: (callback, value, meta) => {
                /* Provide file and text for the link dialog */
                if (meta.filetype === "file") {
                    callback("https://www.google.com/logos/google.jpg", {
                        text: "My text",
                    });
                }

                /* Provide image and alt text for the image dialog */
                if (meta.filetype === "image") {
                    callback("https://www.google.com/logos/google.jpg", {
                        alt: "My alt text",
                    });
                }

                /* Provide alternative source and posted for the media dialog */
                if (meta.filetype === "media") {
                    callback("movie.mp4", {
                        source2: "alt.ogg",
                        poster: "https://www.google.com/logos/google.jpg",
                    });
                }
            },
            height: 600,
            image_caption: true,
            quickbars_selection_toolbar:
                "bold italic | quicklink h2 h3 blockquote quickimage quicktable",
            noneditable_class: "mceNonEditable",
            toolbar_mode: "sliding",
            contextmenu: "link image table",
            skin: "oxide-dark",
            // content_css: "dark",
            content_style:
                "body { font-family:Helvetica,Arial,sans-serif; font-size:16px }",
        });

        $(document).ready(function () {
            $('#category_id').change(function () {
                var category_id = $(this).val();
                if (category_id) {
                    $.ajax({
                        type: 'POST',
                        url: 'AddPost.php',
                        data: { category_id: category_id, fetch_subcategories: true },
                        success: function (response) {
                            $('#subcategory_id').html(response);
                        }
                    });
                } else {
                    $('#subcategory_id').html('<option value="">Select a subcategory</option>');
                }
            });
        });
    </script>
</body>

</html>