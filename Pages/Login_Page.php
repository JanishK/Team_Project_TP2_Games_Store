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
					header("Location:home_Page.php");
					exit();
					}
				} else {
					//error message for invalid password entered 
					$error_message = 'Error logging in, password does not match.' ;
				}
				}else {
			 //error message for invalid username entered
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
	<link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
	<link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png" />
	<script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
</head>
<body class="<?php echo $themeClass; ?>">
            <!-- NAVIGATION BAR -->
    <nav class="cb-nav">
        <div class="cb-nav__container">
            
            <!-- Brand -->
            <a class="cb-brand" href="./home_Page.php">
            <img class="cb-brand__logo" src="/Team_Project_TP2_Games_Store/Assets/Logo.png" alt="CoreByte Logo" />
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

                <a href="./basket_Page.php" role="menuitem">Basket <span class="notification">1</span></a>
                <a href="./registration_page.php" role="menuitem">Account</a>
          		<a href="./settingsPage.php" role="menuitem">Settings</a>
                <a href="./contactUs_Page.php" role="menuitem">Support</a>
                <a href="#" role="menuitem">Sign out</a>
            </div>
            </div>

        </div>
    </nav>
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
			Username: <input type="text" name="username" placeholder="Enter username" required>
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