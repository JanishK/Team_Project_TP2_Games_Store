<?php 
  $error_message = '';
  $success_message = '';
//if the form has been submitted
if (isset($_POST['submitted'])){
 #prepare the form input

  // connect to the database
  require_once('connectdb.php');
 
  $username=isset($_POST['username'])? trim($_POST['username']):false;
  $password=isset($_POST['password'])? trim($_POST['password']):false;
  $password_confirm=isset($_POST['Confirm_password'])? trim($_POST['Confirm_password']):false;
  $email=isset($_POST['email'])? trim($_POST['email']):false;
  if (!($username)){
	$error_message = "Please enter a valid Username!";
	}
    elseif(!($password)){
        $error_message = "Please enter a valid Password!";
    }
    elseif(!($password_confirm)){
        $error_message = "Please confirm your Password!";
    }
  elseif (($password !== $password_confirm)) {
    $error_message = "passwords do not match!";
  }
	elseif (!($email)) {
       $error_message = "Please enter a valid email!";
    }
    else{
    // all is well, proceed to register the user
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
 try{
	
	#register user by inserting the user info 
	$stat=$db->prepare("insert into users (username, password, email,is_admin) VALUES (?, ?, ?, ?)");
	$stat->execute(array($username, $password_hash,$email,0));
	
	$id=$db->lastInsertId();
	$success_message = "Congratulations $username! you are now registered. ";  	
	
 }
 catch (PDOException $ex){
	$error_message = "Sorry, a database error occurred! <br>";
	$error_message = "Error details: <em>". $ex->getMessage()."</em>";
 }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="icon" type="image/png" href="/Assets/Logo.png">
</head>
<body>
    <div class="nav-bar">
        <ul class="nav-left">
            <img class="page_logo" src="../Assets/Logo.png" alt="">
            <li><a href="./home_Page.html">Home</a></li>
            <li><a href="./Products_Page.html">Products</a></li>
            <li><a href="./aboutUs_Page.html">About</a></li>
        </ul>

        <ul class="nav-right">
            <li><a href="./contact_us.html"><img src="../Assets/Support.svg" class="basket-icon" alt=""></a></li>
            <li><a href="./Login_Page.php"><img src="../Assets/Account.svg" class="basket-icon" alt=""></a></li>
            <li><a href="./basket_Page.html">
                <img src="../Assets/Basket.svg" class="basket-icon" />
            </a></li>
        </ul>
    </div>

    <div class="register-container">
        <h1>Register Page</h1>
        <p>Please fill in the form below to create an account.</p>
        <?php
        if (!empty($error_message)){
            echo '<div class="error-message">' . $error_message . '</div>';
    }
        if (!empty($success_message)){
            echo '<div class="success-message">' . $success_message . '</div>';
        }
        ?>
        <div class="registration-form">
            <form method = "post" action="registration_page.php">
	            Email:<input type="email" name="email" placeholder = "Enter Email" required /><br>
	            Username: <input type="text" name="username" placeholder="Enter username" required /><br>
	            Password: <input type="password" name="password" placeholder = "Enter password" required /><br>
                Confirm Password: <input type="password" name="Confirm_password" placeholder="Confirm password" required /><br>
	        <input type="submit" value="Register" /> 
	        <input type="hidden" name="submitted" value="true"/> 
        </div>

  </form>  
  <p> Already a user? <a href="login_Page.php">Log in</a>  </p>
    </div>
</body>
</html>