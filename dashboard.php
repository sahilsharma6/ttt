<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once 'db.php'; // Include your database connection file
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'SuperAdmin' && $_SESSION['role'] != 'Operator')) {
    header('Location: login.php');
    exit();
}


// $user_id = $_SESSION['user_id'];


// Fetch usernames from the database excluding the logged-in user


// $user_id = $_SESSION['user_id'];
// echo 'dscnxjemfdkfvjncxkndfkcn ' . $role . ' ' . $user_id;


// // Fetch the username of the logged-in user
// $stmt = mysqli_prepare($connection, "SELECT username FROM testt WHERE id = ?");
// mysqli_stmt_bind_param($stmt, "i", $user_id);
// mysqli_stmt_execute($stmt);
// mysqli_stmt_bind_result($stmt, $username);

// mysqli_stmt_fetch($stmt); // Fetch the result into variables
// mysqli_stmt_close($stmt);
// mysqli_close($connection);

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Main Page</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <div class="wrapper" style="  background:; width: 100vw; height: ;">


        <?php include_once 'sidebar.php'; ?>

        <div class="dash-content">
            <h2>Welcome to Dashboard
                <span class="text-danger">
                    <?php echo $role; ?>
                </span>
            </h2>
        </div>
    </div>
</body>

</html>