<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $token = $_GET['token'];

    // Verify the token
    $stmt = mysqli_prepare($connection, "SELECT id, reset_token_expiry FROM testt WHERE reset_token = ?");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $expiry);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($expiry < date("Y-m-d H:i:s")) {
        echo "Token has expired.";
    } else {
        // Update the password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = mysqli_prepare($connection, "UPDATE testt SET pass = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $passwordHash, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo "Password has been updated.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <style>
        /* Your CSS styles from the previous page */
    </style>
</head>

<body>
    <div class="wrapper">
        <header>Reset Password</header>
        <form action="reset-password.php?token=<?php echo $_GET['token']; ?>" method="POST">
            <div class="field password">
                <div class="input-area">
                    <input type="password" placeholder="New Password" name="password">
                    <i class="icon fas fa-lock"></i>
                </div>
                <div class="error error-txt">Password can't be blank</div>
            </div>
            <input type="submit" value="Reset Password">
        </form>
    </div>
</body>

</html>