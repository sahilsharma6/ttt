<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once 'db.php'; // Include your database connection file
include_once 'features/toast.html';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'SuperAdmin' && $_SESSION['role'] != 'Operator')) {
    header('Location: login.php');
    exit();
}


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
            echo "<script>showToast('success', 'New user registered successfully!')</script>";
            ;
        } else {
            $error = "Error registering user: " . mysqli_error($connection);
            echo "<script>showToast('error', '<?php echo $error; ?>')</script>";

        }
    }
}



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



    <?php include_once 'sidebar.php'; ?>


    <div class="dash-content">

        <?php if ($_SESSION['role'] == 'SuperAdmin'): ?>
            <div class="mt-5">
                <h3>Register a New Admin or Operator</h3>
                <form action="create-user.php" method="POST">
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