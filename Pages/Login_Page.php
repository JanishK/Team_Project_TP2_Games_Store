<?php
	$error_message = '';
	// Start the session
	session_start();
	$loggedin = isset($_SESSION['username']);
	if (isset($_POST['submitted'])){
		// validate that both username and password fields are filled
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
					header("Location:admin_panel.php");
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
            <img class="page_logo" src="../Assets/Logo.png" alt="">
            <li><a href="./home_Page.html">Home</a></li>
            <li><a href="./Products_Page.php">Products</a></li>
            <li><a href="./aboutUs_Page.html">About</a></li>
        </ul>

        <ul class="nav-right">
            <li><a href="./contactUs_Page.html"><img src="../Assets/Support.svg" class="basket-icon" alt=""></a></li>
            <li><a href="./Login_Page.php"><img src="../Assets/Account.svg" class="basket-icon" alt=""></a></li>
            <li><a href="./basket_Page.php">
                <img src="../Assets/Basket.svg" class="basket-icon" />
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
        
		<!-- Login form -->
        <div class="login-form">
		<?php if ($loggedin == false): ?>
        <form action="Login_Page.php" method="post">
		<p>Please enter your credentials to log in.</p>
		<!-- username input -->
        username: <input type="text" name="username" placeholder="Enter username" required>
		<!-- password input -->
        Password: <input type="password" name="password" placeholder="Enter password" required>
		<!-- submit button -->
        <input type="submit" value="Login">
        <input type="hidden" name="submitted" value="true">
        <!-- register link -->
        <p>Do not have an account? <a href="./registration_page.php">Register</a></p>
         </div>
        </form>
		<?php else: ?>
		<p>You are already logged in as <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
		<p><a href="logout.php">Log out</a></p>
		<?php endif; ?>
    </div>
</body>
</html>