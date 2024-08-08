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

// Get search, sort and filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

// Build the SQL query with search and sort functionality
$sort_order = $sort === 'oldest' ? 'ASC' : 'DESC';
$search_query = !empty($search) ? " AND (username LIKE '%$search%' OR email LIKE '%$search%')" : '';

$query = "SELECT id, username, email, role FROM testt WHERE 1=1 $search_query ORDER BY id $sort_order LIMIT $offset, $records_per_page";
$result = mysqli_query($connection, $query);

// Get total records for pagination
$total_query = "SELECT COUNT(*) AS total FROM testt WHERE 1=1 $search_query";
$total_result = mysqli_query($connection, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);
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
                <div class="col-md-4 ">
                    <input type="text" name="search" placeholder="Search by username or email"
                        value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <select name="sort" class="form-control">
                        <option value="latest" <?php if ($sort == 'latest')
                            echo 'selected'; ?>>Latest</option>
                        <option value="oldest" <?php if ($sort == 'oldest')
                            echo 'selected'; ?>>Oldest</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="records_per_page" class="form-control">
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

        <table class="text-center">
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                    <td class="actions">
                        <form action="manage.php" method="POST">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <select name="role">
                                <option value="User" <?php if ($row['role'] == 'User')
                                    echo 'selected'; ?>>User</option>
                                <option value="Admin" <?php if ($row['role'] == 'Admin')
                                    echo 'selected'; ?>>Admin</option>
                                <?php if ($row['role'] == 'SuperAdmin'): ?>
                                    <option value="SuperAdmin" selected>SuperAdmin</option>
                                <?php endif; ?>
                            </select>
                            <?php if ($row['role'] == 'SuperAdmin'): ?>
                                <button type="submit" disabled>Can't Update</button>
                            <?php else: ?>
                                <button type="submit" name="update_role">Update Role</button>
                            <?php endif; ?>
                        </form>

                        <form action="manage.php" method="POST">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <input type="submit" name="delete_user" value="Delete User" <?php if ($row['role'] == 'SuperAdmin')
                                echo 'disabled'; ?>>
                        </form>

                        <form action="manage.php" method="POST">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <?php if ($row['role'] == 'User'):
                                echo 'Super admin should not change user password';
                            else: ?>
                                <input type="password" name="new_password" placeholder="New Password" required>
                                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                                <button type="submit" name="change_password">Change Password</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Pagination Links -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a
                    href="manage.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&records_per_page=<?php echo $records_per_page; ?>">&laquo;
                    Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="manage.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&records_per_page=<?php echo $records_per_page; ?>"
                    class="<?php if ($i == $page)
                        echo 'active'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a
                    href="manage.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&records_per_page=<?php echo $records_per_page; ?>">Next
                    &raquo;</a>
            <?php endif; ?>
        </div>

    </div>
</body>

</html>