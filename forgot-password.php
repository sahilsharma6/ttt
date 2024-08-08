<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Generate a unique token
    $token = bin2hex(random_bytes(50));
    $expiry = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token valid for 1 hour

    // Insert token into the database
    $stmt = mysqli_prepare($connection, "UPDATE testt SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "sss", $token, $expiry, $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Send reset email
    $resetLink = "http://yourdomain.com/reset-password.php?token=$token";
    $subject = "Password Reset Request";
    $message = "To reset your password, please click the link below:\n\n$resetLink";
    mail($email, $subject, $message);

    echo "A password reset link has been sent to your email.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <style>
        /* Your CSS styles from the previous page */
    </style>
</head>

<body>
    <div class="wrapper">
        <header>Forgot Password</header>
        <form action="forgot-password.php" method="POST">
            <div class="field email">
                <div class="input-area">
                    <input type="email" placeholder="Email" name="email">
                    <i class="icon fas fa-envelope"></i>
                </div>
                <div class="error error-txt">Please enter a valid email</div>
            </div>
            <input type="submit" value="Send Reset Link">
        </form>
        <div class="sign-txt">Remembered? <a href="./login.php">Login</a></div>
    </div>
</body>

</html>