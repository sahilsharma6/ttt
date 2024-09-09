<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'SuperAdmin') {
    header('Location: login.php');
    exit();
}

// Pagination settings
$records_per_page = isset($_GET['records_per_page']) ? (int) $_GET['records_per_page'] : 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get search, sort, and filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$role_filter = isset($_GET['role_filter']) ? $_GET['role_filter'] : '';

// Build the SQL query with search, sort, and role filter functionality
$sort_order = $sort === 'oldest' ? 'ASC' : 'DESC';
$search_query = !empty($search) ? " AND (username LIKE '%$search%' OR email LIKE '%$search%')" : '';
$role_query = !empty($role_filter) ? " AND role = '$role_filter'" : '';

$query = "SELECT id, username, email, role FROM testt WHERE role IN ('Admin', 'Operator', 'SuperAdmin') $search_query $role_query ORDER BY id $sort_order LIMIT $offset, $records_per_page";
$result = mysqli_query($connection, $query);

// Get total records for pagination
$total_query = "SELECT COUNT(*) AS total FROM testt WHERE role IN ('Admin', 'Operator', 'SuperAdmin') $search_query $role_query";
$total_result = mysqli_query($connection, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Handle registration of a new Admin or Operator
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = mysqli_real_escape_string($connection, $_POST['password']);
    $role = mysqli_real_escape_string($connection, $_POST['role']);

    // Check if the email already exists
    $check_email_query = "SELECT * FROM testt WHERE email = '$email'";
    $check_email_result = mysqli_query($connection, $check_email_query);
    if (mysqli_num_rows($check_email_result) > 0) {
        $error = "Email already exists!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO testt (username, email, pass, role) VALUES ('$username', '$email', '$hashed_password', '$role')";
        if (mysqli_query($connection, $insert_query)) {
            $success = "New user registered successfully!";
        } else {
            $error = "Error registering user: " . mysqli_error($connection);
        }
    }
}

// Handle updating the role of an existing user
if (isset($_POST['update_role'])) {
    $user_id = (int) $_POST['user_id'];
    $role = mysqli_real_escape_string($connection, $_POST['role']);
    $update_query = "UPDATE testt SET role = '$role' WHERE id = $user_id";
    if (mysqli_query($connection, $update_query)) {
        $success = "User role updated successfully!";
    } else {
        $error = "Error updating role: " . mysqli_error($connection);
    }
}

// Handle deleting a user
if (isset($_POST['delete_user'])) {
    $user_id = (int) $_POST['user_id'];
    $delete_query = "DELETE FROM testt WHERE id = $user_id";
    if (mysqli_query($connection, $delete_query)) {
        $success = "User deleted successfully!";
    } else {
        $error = "Error deleting user: " . mysqli_error($connection);
    }
}

// Handle changing the password of an existing user
if (isset($_POST['change_password'])) {
    $user_id = (int) $_POST['user_id'];
    $new_password = mysqli_real_escape_string($connection, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($connection, $_POST['confirm_password']);

    if ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_password_query = "UPDATE testt SET pass = '$hashed_password' WHERE id = $user_id";
        if (mysqli_query($connection, $update_password_query)) {
            $success = "Password changed successfully!";
        } else {
            $error = "Error changing password: " . mysqli_error($connection);
        }
    } else {
        $error = "Passwords do not match!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        .actions {
            display: flex;
            gap: 10px;
        }

        .actions form {
            display: inline;
        }

        .pagination {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }

        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .pagination a.active {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .pagination a:hover {
            background-color: #ddd;
        }
    </style>
</head>

<body>
    <?php include_once 'sidebar.php'; ?>
    <div class="dash-content">

        <!-- Filters and Search -->
        <form action="manage.php" method="GET">
            <div class="row mb-3 mt-4">
                <div class="col-md-3">
                    <input type="text" name="search" placeholder="Search by username or email"
                        value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <select name="sort" class="form-select">
                        <option value="latest" <?php if ($sort == 'latest')
                            echo 'selected'; ?>>Latest</option>
                        <option value="oldest" <?php if ($sort == 'oldest')
                            echo 'selected'; ?>>Oldest</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="role_filter" class="form-select">
                        <option value="" <?php if ($role_filter == '')
                            echo 'selected'; ?>>All Roles</option>
                        <option value="Admin" <?php if ($role_filter == 'Admin')
                            echo 'selected'; ?>>Admin</option>
                        <option value="Operator" <?php if ($role_filter == 'Operator')
                            echo 'selected'; ?>>Operator
                        </option>
                        <option value="SuperAdmin">
                            <?php if ($role_filter == 'SuperAdmin')
                                echo 'selected'; ?>SuperAdmin
                        </option>

                    </select>
                </div>
                <div class="col-md-3">
                    <select name="records_per_page" class="form-select">
                        <option value="10" <?php if ($records_per_page == 10)
                            echo 'selected'; ?>>10 per page</option>
                        <option value="25" <?php if ($records_per_page == 25)
                            echo 'selected'; ?>>25 per page</option>
                        <option value="50" <?php if ($records_per_page == 50)
                            echo 'selected'; ?>>50 per page</option>
                    </select>
                </div>
                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </div>
        </form>

        <!-- Display Success or Error Messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Display User Table -->
        <table class="table table-striped text-center mt-4">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td class="actions">
                            <form action="manage.php" method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <select name="role" class="btn btn-secondary" <?php if ($row['role'] == 'SuperAdmin')
                                    echo 'disabled'; ?>>
                                    <option value="Admin" <?php if ($row['role'] == 'Admin')
                                        echo 'selected'; ?>>Admin
                                    </option>
                                    <option value="Operator" <?php if ($row['role'] == 'Operator')
                                        echo 'selected'; ?>>
                                        Operator</option>
                                    <?php if ($row['role'] == 'SuperAdmin'): ?>

                                        <option value="SuperAdmin" <?php echo 'selected'; ?>>
                                            SuperAdmin</option>
                                    <?php endif; ?>
                                </select>



                                <?php if ($row['role'] == 'SuperAdmin'): ?>
                                    <button type="submit" class="btn btn-primary" name="update_role" disabled>disabled
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-primary" name="update_role">Update
                                        Role</button>
                                <?php endif ?>
                            </form>

                            <form action="manage.php" method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <?php if ($row['role'] == 'SuperAdmin'): ?>
                                    <button type="submit" name="delete_user" class="btn btn-danger" disabled>Delete
                                        User</button>
                                <?php else: ?>
                                    <button type="submit" name="delete_user" class="btn btn-danger">Delete User</button>
                                <?php endif; ?>
                            </form>

                            <form action="manage.php" method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <input type="password" name="new_password" placeholder="New Password" class="form-control ">
                                <input type="password" name="confirm_password" placeholder="Confirm Password"
                                    class="form-control   mt-2">
                                <button type="submit" name="change_password" class="btn btn-warning  mt-2">Update
                                    Password</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&sort=<?php echo $sort; ?>&role_filter=<?php echo $role_filter; ?>&records_per_page=<?php echo $records_per_page; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page)
                        echo 'active'; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&sort=<?php echo $sort; ?>&role_filter=<?php echo $role_filter; ?>&records_per_page=<?php echo $records_per_page; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&sort=<?php echo $sort; ?>&role_filter=<?php echo $role_filter; ?>&records_per_page=<?php echo $records_per_page; ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- User Registration Form (SuperAdmin only) -->
        <?php if ($_SESSION['role'] == 'SuperAdmin'): ?>
            <div class="mt-5">
                <h3>Register a New Admin or Operator</h3>
                <form action="manage.php" method="POST">
                    <div class="mb-3 w-25">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control " required>
                    </div>
                    <div class="mb-3 w-25">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3 w-25">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3 w-25">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" name="role" class="form-select">
                            <option value="Admin">Admin</option>
                            <option value="Operator">Operator</option>
                        </select>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary">Register</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>