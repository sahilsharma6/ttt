<?php
session_start();
require_once 'db.php';

// Check if the user is logged in and has the right role
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header("Location: index.php");
    exit();
}

$error = ''; // Initialize an error variable
$success = ''; // Initialize a success variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs
    $category_name = trim($_POST['category_name']);
    $subcategory_names = array_map('trim', $_POST['subcategory_name']);
    $category_image = $_FILES['category_image'];

    // Check for errors
    if (empty($category_name)) {
        $error = "Category name is required.";
    } elseif ($category_image['error'] == UPLOAD_ERR_NO_FILE) {
        $error = "Category image is required.";
    } elseif (!in_array($category_image['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
        $error = "Invalid image format. Only JPG, PNG, and GIF are allowed.";
    } elseif ($category_image['size'] > 2 * 1024 * 1024) { // 2MB limit
        $error = "Image size should not exceed 2MB.";
    } elseif (empty(array_filter($subcategory_names))) {
        $error = "At least one subcategory is required.";
    } else {
        // Check if the category already exists
        $stmt = mysqli_prepare($connection, "SELECT id FROM categories WHERE category_name = ?");
        mysqli_stmt_bind_param($stmt, "s", $category_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Category already exists.";
        } else {
            // Handle image upload
            $image_path = 'uploads/' . basename($category_image['name']);
            if (move_uploaded_file($category_image['tmp_name'], $image_path)) {
                // Insert the category into the database
                $stmt = mysqli_prepare($connection, "INSERT INTO categories (category_name, category_image) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "ss", $category_name, $image_path);

                if (mysqli_stmt_execute($stmt)) {
                    $category_id = mysqli_insert_id($connection);

                    // Insert subcategories into the database
                    foreach ($subcategory_names as $subcategory_name) {
                        if (!empty($subcategory_name)) {
                            $stmt = mysqli_prepare($connection, "INSERT INTO subcategories (category_id, subcategory_name) VALUES (?, ?)");
                            mysqli_stmt_bind_param($stmt, "is", $category_id, $subcategory_name);
                            mysqli_stmt_execute($stmt);
                        }
                    }
                    $success = "Category and subcategories added successfully!";
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            } else {
                $error = "Failed to upload image.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}
mysqli_close($connection);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Category and Subcategory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #3c41a1;
        }

        .wrapper {
            width: 380px;
            padding: 40px 30px 50px 30px;
            background: #fff;
            border-radius: 5px;
            text-align: center;
            box-shadow: 10px 10px 15px rgba(0, 0, 0, 0.1);
        }

        .wrapper header {
            font-size: 35px;
            font-weight: 600;
        }

        .wrapper form {
            margin: 40px 0;
        }

        form .field {
            width: 100%;
            margin-bottom: 20px;
        }

        form .field.shake {
            animation: shake 0.3s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                margin-left: 0px;
            }

            20%,
            80% {
                margin-left: -12px;
            }

            40%,
            60% {
                margin-left: 12px;
            }
        }

        form .field .input-area {
            height: 50px;
            width: 100%;
            position: relative;
        }

        form input,
        form select {
            width: 100%;
            height: 100%;
            outline: none;
            padding: 0 45px;
            font-size: 18px;
            background: none;
            caret-color: #5372F0;
            border-radius: 5px;
            border: 1px solid #bfbfbf;
            border-bottom-width: 2px;
            transition: all 0.2s ease;
        }

        form .field input:focus,
        form .field select:focus,
        form .field.valid input,
        form .field.valid select {
            border-color: #5372F0;
        }

        form .field.shake input,
        form .field.error input,
        form .field.shake select,
        form .field.error select {
            border-color: #dc3545;
        }

        .field .input-area i {
            position: absolute;
            top: 50%;
            font-size: 18px;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .input-area .icon {
            left: 15px;
            color: #bfbfbf;
            transition: color 0.2s ease;
        }

        .input-area .error-icon {
            right: 15px;
            color: #dc3545;
        }

        form input:focus~.icon,
        form select:focus~.icon,
        form .field.valid .icon {
            color: #5372F0;
        }

        form .field.shake input:focus~.icon,
        form .field.error input:focus~.icon,
        form .field.shake select:focus~.icon,
        form .field.error select:focus~.icon {
            color: #bfbfbf;
        }

        form input::placeholder,
        form select::placeholder {
            color: #bfbfbf;
            font-size: 17px;
        }

        form .field .error-txt {
            color: #dc3545;
            text-align: left;
            margin-top: 5px;
        }

        form .field .error {
            display: none;
        }

        form .field.shake .error,
        form .field.error .error {
            display: block;
        }

        form input[type="submit"] {
            height: 50px;
            margin-top: 30px;
            color: #fff;
            padding: 0;
            border: none;
            background: #5372F0;
            cursor: pointer;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        form input[type="submit"]:hover {
            background: #2c52ed;
        }

        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 1s;
            display: none;
            z-index: 1000;
        }

        .alert.error {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <header>Add Category and Subcategory</header>
        <form action="AddCategories.php" method="POST" enctype="multipart/form-data">
            <div class="field">
                <div class="input-area">
                    <input type="text" placeholder="Category Name" name="category_name" required>
                    <i class="icon fas fa-tag"></i>
                    <i class="error error-icon fas fa-exclamation-circle"></i>
                </div>
                <div class="error error-txt">Category name can't be blank</div>
            </div>
            <div id="subcategory-fields">
                <div class="field">
                    <div class="input-area">
                        <input type="text" placeholder="Subcategory Name" name="subcategory_name[]" required>
                        <i class="icon fas fa-tags"></i>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addSubcategoryField()" class=""
                style="margin-block: 10px;padding: 10px 20px;">Add Another
                Subcategory</button>
            <div class="field">
                <div class="input-area">
                    <input type="file" name="category_image" required>
                    <i class="icon fas fa-image"></i>
                    <i class="error error-icon fas fa-exclamation-circle"></i>
                </div>
                <div class="error error-txt">Category image is required.</div>
            </div>
            <input type="submit" value="Add Category">
        </form>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="alert"><?php echo $success; ?></div>
        <?php endif; ?>
        <div class="sign-txt"><a href="dashboard.php">Back to Dashboard</a></div>
    </div>

    <script>
        // Function to add another subcategory field
        function addSubcategoryField() {
            const subcategoryFields = document.getElementById('subcategory-fields');
            const newField = document.createElement('div');
            newField.classList.add('field');
            newField.innerHTML = `
                <div class="input-area">
                    <input type="text" placeholder="Subcategory Name" name="subcategory_name[]" required>
                    <i class="icon fas fa-tags"></i>
                </div>
            `;
            subcategoryFields.appendChild(newField);
        }

        // Function to close the alert box
        const closeAlert = () => {
            const alertBox = document.getElementsByClassName("alert")[0];
            if (alertBox) {
                alertBox.style.display = "none";
            }
        }

        // Automatically hide alert after 5 seconds
        setTimeout(closeAlert, 5000);

        // Show alert box if there's a message
        const alertBox = document.getElementsByClassName("alert")[0];
        if (alertBox) {
            alertBox.style.display = "block";
        }
    </script>
</body>

</html>