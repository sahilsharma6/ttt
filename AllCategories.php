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

// Handle deletion of category
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    $stmt = mysqli_prepare($connection, "DELETE FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    if (mysqli_stmt_execute($stmt)) {
        $success = "Category deleted successfully!";
    } else {
        $error = "Something went wrong. Please try again.";
    }
    mysqli_stmt_close($stmt);
}

// Handle update of category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['category_name']);
    $stmt = mysqli_prepare($connection, "UPDATE categories SET category_name = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $category_name, $category_id);
    if (mysqli_stmt_execute($stmt)) {
        $success = "Category updated successfully!";
    } else {
        $error = "Something went wrong. Please try again.";
    }
    mysqli_stmt_close($stmt);
}

// Fetch all categories
$result = mysqli_query($connection, "SELECT * FROM categories");

mysqli_close($connection);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Categories</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <style>
        /* Add your CSS styles here */
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

        .container {
            width: 800px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 10px 10px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .btn-edit {
            background-color: #4CAF50;
            color: white;
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
        }

        .alert {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        .alert.error {
            background-color: #f44336;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Manage Categories</h1>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="alert"><?php echo $success; ?></div>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td>
                            <form action="AllCategories.php" method="POST" style="display: inline;">
                                <input type="text" name="category_name" value="<?php echo $row['category_name']; ?>"
                                    required>
                                <input type="hidden" name="category_id" value="<?php echo $row['id']; ?>">
                                <input type="submit" name="update" value="Update" class="btn btn-edit">
                            </form>
                        </td>
                        <td>
                            <a href="AllCategories.php?delete=<?php echo $row['id']; ?>" class="btn btn-delete"
                                onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</body>

</html>