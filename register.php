<?php
session_start();
require_once 'config.php';

$error = ''; // Initialize an error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = 'User'; // Default role is 'User'
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Check if username already exists
    $stmt = mysqli_prepare($connection, "SELECT id FROM testt WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error = "Username already exists. Please choose a different username.";
    } else {
        // Check if email already exists
        $stmt = mysqli_prepare($connection, "SELECT id FROM testt WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Email already exists. Please choose a different email.";
        } else {
            // Insert new user
            $stmt = mysqli_prepare($connection, "INSERT INTO testt (username, email, pass, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $passwordHash, $role);

            if (mysqli_stmt_execute($stmt)) {
                // Registration successful, show alert
                echo '<script>window.onload = function() { document.getElementById("custom-alert").style.display = "block"; }</script>';
                echo '<script>setTimeout(function() { document.getElementById("custom-alert").style.display = "none"; }, 5000);</script>';
                $error = "Registration successful.";
            } else {
                $error = "Registration failed.";
            }
        }
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"/>
    <style>
        /* Add your CSS styles here */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
        i{
            cursor: pointer;
        }
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body{
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #3c41a1;
        }
        ::selection{
            color: #fff;
            background: #5372F0;
        }
        .wrapper{
            width: 380px;
            padding: 40px 30px 50px 30px;
            background: #fff;
            border-radius: 5px;
            text-align: center;
            box-shadow: 10px 10px 15px rgba(0,0,0,0.1);
        }
        .wrapper header{
            font-size: 35px;
            font-weight: 600;
        }
        .wrapper form{
            margin: 40px 0;
        }
        form .field{
            width: 100%;
            margin-bottom: 20px;
        }
        form .field.shake{
            animation: shake 0.3s ease-in-out;
        }
        @keyframes shake {
            0%, 100%{
                margin-left: 0px;
            }
            20%, 80%{
                margin-left: -12px;
            }
            40%, 60%{
                margin-left: 12px;
            }
        }
        form .field .input-area{
            height: 50px;
            width: 100%;
            position: relative;
        }
        form input{
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
        form .field.valid input{
            border-color: #5372F0;
        }
        form .field.shake input,
        form .field.error input{
            border-color: #dc3545;
        }
        .field .input-area i{
            position: absolute;
            top: 50%;
            font-size: 18px;
            pointer-events: none;
            transform: translateY(-50%);
        }
        .input-area .icon{
            left: 15px;
            color: #bfbfbf;
            transition: color 0.2s ease;
        }
        .input-area .error-icon{
            right: 15px;
            color: #dc3545;
        }
        form input:focus ~ .icon,
        form .field.valid .icon{
            color: #5372F0;
        }
        form .field.shake input:focus ~ .icon,
        form .field.error input:focus ~ .icon{
            color: #bfbfbf;
        }
        form input::placeholder{
            color: #bfbfbf;
            font-size: 17px;
        }
        form .field .error-txt{
            color: #dc3545;
            text-align: left;
            margin-top: 5px;
        }
        form .field .error{
            display: none;
        }
        form .field.shake .error,
        form .field.error .error{
            display: block;
        }
        form input[type="submit"]{
            height: 50px;
            margin-top: 30px;
            color: #fff;
            padding: 0;
            border: none;
            background: #5372F0;
            cursor: pointer;
            border-bottom: 2px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        form input[type="submit"]:hover{
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 1000;

            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <header>Register</header>
    <form action="register.php" method="POST">
        <div class="field">
            <div class="input-area">
                <input type="text" placeholder="Username" name="username" required>
                <i class="icon fas fa-user"></i>
                <i class="error error-icon fas fa-exclamation-circle"></i>
            </div>
            <div class="error error-txt">Username can't be blank</div>
        </div>
        <div class="field">
            <div class="input-area">
                <input type="email" placeholder="Email" name="email" required>
                <i class="icon fas fa-envelope"></i>
                <i class="error error-icon fas fa-exclamation-circle"></i>
            </div>
            <div class="error error-txt">Email can't be blank</div>
        </div>
        <div class="field">
            <div class="input-area">
                <input type="password" placeholder="Password" name="password" required>
                <i class="icon fas fa-lock"></i>
                <i class="error error-icon fas fa-exclamation-circle"></i>
            </div>
            <div class="error error-txt">Password can't be blank</div>
        </div>
        <input type="submit" value="Register">
    </form>
    <div class="sign-txt">Already a member? <a href="login.php">Login</a></div>
</div>

<div id="custom-alert" class="alert">
    <div>
        <?php echo $error; ?>
        <i class="fa-solid fa-xmark" style="margin-left: 10px;" onclick="closeAlert()"></i>
    </div>
</div>

<script>
    // Function to close the alert box
    const closeAlert = () => {
        const alertBox = document.getElementById("custom-alert");
        alertBox.style.display = "none";
    }
</script>

</body>
</html>
