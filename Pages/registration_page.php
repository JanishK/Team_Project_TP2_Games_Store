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
        <!-- NAVIGATION BAR -->
    <nav class="cb-nav">
        <div class="cb-nav__container">
            
            <!-- Brand -->
            <a class="cb-brand" href="./home_Page.html">
            <img class="cb-brand__logo" src="/Assets/Logo.png" alt="CoreByte Logo" />
            <span class="cb-brand__text">CoreByte</span>
            </a>

            <!-- Main links -->
            <ul class="cb-links" id="cbNavLinks">
                <li><a href="./home_Page.php" class="cb-link is-active">Home</a></li>
                <li><a href="./Products_Page.php" class="cb-link">Products</a></li>
                <li><a href="./aboutUs_Page.php" class="cb-link">About</a></li>
            </ul>

            <!-- User avatar dropdown -->
            <div class="cb-user">
            <button class="cb-user__btn" type="button" id="cbUserBtn" aria-expanded="false" aria-controls="cbUserMenu">
                <span class="sr-only">Open user menu</span>
                <img
                class="cb-user__avatar"
                src="https://flowbite.com/docs/images/people/profile-picture-5.jpg"
                alt="User photo"
                />
            </button>



            <div class="cb-user__menu hidden" id="cbUserMenu" role="menu">
                <div class="cb-user__header">
                <span class="cb-user__name">Janish Kandel</span>
                <span class="cb-user__email">JanishK@corebyte.com</span>
                </div>

                <a href="./basket_Page.html" role="menuitem">Basket <span class="notification">1</span></a>
                <a href="./registration_page.php" role="menuitem">Account</a>
                <a href="#" role="menuitem">Settings</a>
                <a href="./contactUs_Page.html" role="menuitem">Support</a>
                <a href="#" role="menuitem">Sign out</a>
            </div>
            </div>

        </div>
    </nav>

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