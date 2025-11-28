<?php
	$error_message = '';
	if (isset($_POST['submitted'])){
	if ( !isset($_POST['username'], $_POST['password']) ) {
		// Could not get the data that should have been sent.
		 exit('Please fill both username and password fields!');
	    }
		require_once ("connectdb.php");
		try {
			//Query DB to find the matching username/password
			// //using prepare/bindparameter to prevent SQL injection.
			$stat = $db->prepare('SELECT password, is_admin FROM users WHERE username = ?');
			$stat->execute(array($_POST['username']));
			// fetch the result row and check
			if ($stat->rowCount()>0){  // matching username
				$row=$stat->fetch();
				if (password_verify($_POST['password'], $row['password'])){ //matching password
					
					//??recording the user session variable and go to loggedin page?? 
				  session_start();
				  //user session variable
					$_SESSION["username"]=$_POST['username'];
					//admin session variable
					$_SESSION["is_admin"]=$row['is_admin'];
					//is admin redirection
					if ($row['is_admin']==1){
					header("Location:admin_panel.html");
					exit();
					}else{
					header("Location:home_Page.html");
					exit();
					}
				} else {
					//error message for invalid password
					$error_message = 'Error logging in, password does not match.' ;
				}
				}else {
			 //error message for invalid username
			  $error_message = 'logging in, Username not found.';
			}
		} catch(PDOException $ex) {
			echo("Failed to connect to the database.<br>");
			echo($ex->getMessage());
			exit;
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
        <img class="page_logo" src="/Assets/Logo.png" alt="">
        <li><a href="./home_Page.html">Home</a></li>
        <li><a href="./Products_Page.html">Products</a></li>
        <li><a href="./aboutUs_Page.html">About</a></li>
    </ul>

    <ul class="nav-right">
        <li><a href="./contact_us.html"><img src="/Assets/Support.svg" class="basket-icon" alt=""></a></li>
        <li><a href="./registration_page.html"><img src="/Assets/Account.svg" class="basket-icon" alt=""></a></li>
        <li><a href="./basket_Page.html">
            <img src="/Assets/Basket.svg" class="basket-icon" />
        </a></li>
    </ul>
</div>
    <div class="login-container">
        <?php
		if (!empty($error_message)){
			echo '<div class="error-message">' . $error_message . '</div>';
		}
		?>
        <h1>Login</h1>
        <p>Please enter your credentials to log in.</p>
        <div class="login-form">
        <form action="Login_Page.php" method="post">
        username: <input type="text" name="username" placeholder="Enter username" required>
        Password: <input type="password" name="password" placeholder="Enter password" required>
        <input type="submit" value="Login">
        <input type="hidden" name="submitted" value="true">
        <p>Do not have an account? <a href="./registration_page.php">Register</a></p>
         </div>
        </form>
    </div>
</body>
</html>