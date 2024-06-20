<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'SuperAdmin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];

    $stmt = mysqli_prepare($connection, "UPDATE user SET role = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $role, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "Role updated successfully.";
    } else {
        echo "Role update failed.";
    }
}

$result = mysqli_query($connection, "SELECT id, username, role FROM user");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Roles</title>
</head>
<body>
    <h2>Manage User Roles</h2>
    <table>
        <tr>
            <th>Username</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <tr>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['role']; ?></td>
                <td>
                    <form action="manage.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                        <select name="role">
                            <option value="User" <?php if ($row['role'] == 'User') echo 'selected'; ?>>User</option>
                            <option value="Admin" <?php if ($row['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
                            <option value="SuperAdmin" <?php if ($row['role'] == 'SuperAdmin') echo 'selected'; ?>>SuperAdmin</option>
                        </select>
                        <button type="submit">Update Role</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <a href="index.php">Back to Home</a>
</body>
</html>
