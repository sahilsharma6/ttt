<?php
session_start();
require_once 'config.php';

$error = ''; // Initialize an error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($connection, "SELECT id, pass, role FROM testt WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $passwordHash, $role);
    mysqli_stmt_fetch($stmt);

    if (password_verify($password, $passwordHash)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        $_SESSION['username'] = $username;
        if ($role == 'User') {
            header('Location: index.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        $error = "Username or password is incorrect."; // Set the error message
    }
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
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
form .pass-txt{
  text-align: left;
  margin-top: -10px;
}
.wrapper a{
  color: #5372F0;
  text-decoration: none;
}
.wrapper a:hover{
  text-decoration: underline;
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
  background-color: #f44336;
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
    <header>Login</header>
    <form action="login.php" method="POST">
      <div class="field email">
        <div class="input-area">
          <input type="text" placeholder="Username" name="username">
          <i class="icon fas fa-user"></i>
          <i class="error error-icon fas fa-exclamation-circle"></i>
        </div>
        <div class="error error-txt">Username can't be blank</div>
      </div>
      <div class="field password">
        <div class="input-area">
          <input type="password" placeholder="Password" name="password">
          <i class="icon fas fa-lock"></i>
          <i class="error error-icon fas fa-exclamation-circle"></i>
        </div>
        <div class="error error-txt">Password can't be blank</div>
      </div>
      <!-- <div class="pass-txt"><a href="#">Forgot password?</a></div> -->
      <input type="submit" value="Login">
    </form>
    <div class="sign-txt">Not yet member? <a href="./register.php">register</a></div>
<div>

</div>

  </div>

<div id="custom-alert" class="alert" >
        <div
        style="
        display:flex;
        justify-content:center;
        align-items:center;
        "
        >

            <?php echo $error; ?> 
            <i class="fa-solid fa-xmark " style="; margin:0 0 0 10px; font-size:23px" onclick="closeAlert()"></i></div>
         
        </div>
<script>
const form = document.querySelector("form");
eField = form.querySelector(".email"),
eInput = eField.querySelector("input"),
pField = form.querySelector(".password"),
pInput = pField.querySelector("input");

form.onsubmit = (e) => {
  // If email and password are blank then add shake class in it else call specified function
  (eInput.value == "") ? eField.classList.add("shake", "error") : checkUserName();
  (pInput.value == "") ? pField.classList.add("shake", "error") : checkPass();

  setTimeout(() => { // Remove shake class after 500ms
    eField.classList.remove("shake");
    pField.classList.remove("shake");
  }, 500);

  eInput.onkeyup = () => { checkUserName(); } // Call checkUserName function on username input keyup
  pInput.onkeyup = () => { checkPass(); } // Call checkPass function on password input keyup

  function checkUserName() { // Check username function
    if (eInput.value == "") { // If username is empty then add error and remove valid class
      eField.classList.add("error");
      eField.classList.remove("valid");
    } else { // If username is not empty then remove error and add valid class
      eField.classList.remove("error");
      eField.classList.add("valid");
    }
  }

  function checkPass() { // Check password function
    if (pInput.value == "") { // If password is empty then add error and remove valid class
      pField.classList.add("error");
      pField.classList.remove("valid");
    } else { // If password is not empty then remove error and add valid class
      pField.classList.remove("error");
      pField.classList.add("valid");
    }
  }

  // If eField and pField don't contain error class, that means user filled details properly
  if (!eField.classList.contains("error") && !pField.classList.contains("error")) {
    // Do nothing here and let the form submit
  } else {
    e.preventDefault(); // Prevent form submission if there are errors
  }
}

window.onload = () => {
    const alertBox = document.getElementById("custom-alert");
    <?php if (!empty($error)): ?>
    setTimeout(() => {
    alertBox.style.display = "block";
  }, 500);
    setTimeout(() => {
    alertBox.style.display = "none";
  }, 5000);

  
//   function closeAlert() {
//   const alertBox = document.getElementById("custom-alert");
//   alertBox.style.display = "none";

//   }

  <?php endif; ?>
}
const closeAlert = () => {
  const alertBox = document.getElementById("custom-alert");
  alertBox.style.display = "none";
}
</script>
</body>
</html>
