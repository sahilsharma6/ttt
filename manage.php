<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'SuperAdmin') {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];

    $stmt = mysqli_prepare($connection, "UPDATE testt SET role = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $role, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "Role updated successfully.";
    } else {
        echo "Role update failed.";
    }
}

$result = mysqli_query($connection, "SELECT id, username, role FROM testt");
?>

<!DOCTYPE html>
<html>

<head>
    <!-- <title>Manage Roles</title> -->

    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php include_once 'sidebar.php'; ?>
    <div class="dash-content">

        <table class="text-center   ">
            <tr class="">
                <th class="">Username</th>
                <th class=" ">Role</th>
                <th class=" ">Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['role']; ?></td>
                    <td>
                        <form action="manage.php" method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <select name="role">
                                <option value="User" <?php if ($row['role'] == 'User')
                                    echo 'selected'; ?>>User</option>
                                <option value="Admin" <?php if ($row['role'] == 'Admin')
                                    echo 'selected'; ?>>Admin</option>
                                <option value="SuperAdmin" <?php if ($row['role'] == 'SuperAdmin')
                                    echo 'selected'; ?>>SuperAdmin
                                </option>
                            </select>
                            <button type="submit">Update Role</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>

</html>