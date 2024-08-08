<?php
session_start();
require_once 'db.php';

// Check if the user is logged in and has the right role
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Get the category ID from URL
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Handle subcategory addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subcategory'])) {
    $subcategory_name = trim($_POST['subcategory_name']);

    if (empty($subcategory_name)) {
        $error = "Subcategory name is required.";
    } else {
        // Check if the subcategory already exists
        $stmt = mysqli_prepare($connection, "SELECT id FROM subcategories WHERE category_id = ? AND subcategory_name = ?");
        mysqli_stmt_bind_param($stmt, "is", $category_id, $subcategory_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Subcategory already exists.";
        } else {
            // Insert the subcategory into the database
            $stmt = mysqli_prepare($connection, "INSERT INTO subcategories (category_id, subcategory_name) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "is", $category_id, $subcategory_name);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Subcategory added successfully!";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle subcategory deletion
if (isset($_GET['delete_subcategory_id'])) {
    $delete_id = intval($_GET['delete_subcategory_id']);

    // Delete the subcategory
    $stmt = mysqli_prepare($connection, "DELETE FROM subcategories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    if (mysqli_stmt_execute($stmt)) {
        $success = "Subcategory deleted successfully!";
    } else {
        $error = "Failed to delete subcategory.";
    }
    mysqli_stmt_close($stmt);
}

// Fetch subcategories for the category
$subcategories = [];
$stmt = mysqli_prepare($connection, "SELECT id, subcategory_name FROM subcategories WHERE category_id = ?");
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $subcategories[] = $row;
}

mysqli_stmt_close($stmt);
mysqli_close($connection);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Subcategories</title>
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
            width: 500px;
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

        .subcategory-list {
            text-align: left;
            margin-top: 20px;
        }

        .subcategory-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .subcategory-list table,
        .subcategory-list th,
        .subcategory-list td {
            border: 1px solid #ddd;
        }

        .subcategory-list th,
        .subcategory-list td {
            padding: 8px;
            text-align: left;
        }

        .subcategory-list tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <header>Manage Subcategories</header>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="alert"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="ManageSubcategories.php?category_id=<?php echo $category_id; ?>" method="POST">
            <div class="field">
                <div class="input-area">
                    <input type="text" placeholder="Subcategory Name" name="subcategory_name" required>
                    <i class="icon fas fa-tags"></i>
                </div>
                <div class="error error-txt">Subcategory name can't be blank</div>
            </div>
            <input type="submit" name="add_subcategory" value="Add Subcategory">
        </form>

        <div class="subcategory-list">
            <h2>Subcategories</h2>
            <table>
                <thead>
                    <tr>
                        <th>Subcategory Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subcategories as $subcategory): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subcategory['subcategory_name']); ?></td>
                            <td>
                                <a href="ManageSubcategories.php?category_id=<?php echo $category_id; ?>&delete_subcategory_id=<?php echo $subcategory['id']; ?>"
                                    onclick="return confirm('Are you sure you want to delete this subcategory?');">
                                    <i class="fas fa-trash" style="color: red;"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="sign-txt"><a href="dashboard.php">Back to Dashboard</a></div>
    </div>

    <script>
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