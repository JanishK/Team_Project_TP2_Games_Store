<?php
$error_message = '';
$success_message = '';
// Start the session
session_start();
// Get is_admin from session
$is_admin = $_SESSION['is_admin'] ?? false;
// Check if user is admin
if($is_admin !== 1){
    // Redirect to home page if not admin
    header("Location: Home_Page.html");
    exit();
}
 // if not logged in, redirect to login page
if(!isset($_SESSION['username'])){
    // Redirect to login page
    header("Location: Login_Page.php");
    exit();
}


//if the form has been submitted
if (isset($_POST['submitted'])){
    // connect to the database
    require_once('connectdb.php');
    //prepare the form input
    $name = $_POST['name'] ?? false;
    $price = $_POST['price'] ?? false;
    $description = $_POST['description'] ?? false;
    $platform = $_POST['platform'] ?? false;
    $age_rating = $_POST['age_rating'] ?? false;
    $platforms = ['PC', 'PS5', 'Xbox', 'Nintendo Switch'];
    $age_ratings = ['8', '13', '16', '18+'];
    //validate platform
    if(!in_array($platform, $platforms)){ $platform = false; }
    //validate age rating
    if(!in_array($age_rating, $age_ratings)){ $age_rating = false; }

    $image = !empty($_FILES['image']['name'])?$_FILES['image']['name']: false;
    //handle file upload
    if($image){
    // directory to save uploaded files
    $target_dir = "images/";
    // target file path
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    // allowed file types
    $allowed_ext = ['jpg','jpeg','png','gif'];
    // get file extension
    $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    // check if file type is allowed
    if (!in_array($ext, $allowed_ext)) {
        $error_message = "Invalid image type. Allowed: jpg, jpeg, png, gif.";
    }else{
    // move uploaded file to target directory
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
}
}
// validate form inputs
if (!($name)){
$error_message = "Please enter a valid Game Name!";
}
elseif(!($platform)){
    $error_message = "Please select a valid Platform!";
}
elseif(!($price)){
    $error_message = "Please enter a valid Price!";
}
elseif(!($age_rating)){
    $error_message = "Please enter a valid Age Rating!";
}
elseif(!($description)){
    $error_message = "Please enter a valid Description!";
}
elseif(!($image)){
    $error_message = "Please upload a valid Image!";
}
else{
try{
    // all is well, proceed to add the game
	$stat=$db->prepare("insert into games (name , description, platform, price, image,age_restriction) VALUES (?, ?, ?, ?, ?, ?)");
    // execute the query
	$stat->execute(array($name,$description,$platform,$price,$image,$age_rating));
    // get the last inserted id
	$id=$db->lastInsertId();
	$success_message = "Game $name has been added successfully. ";  	
	
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
    <title>Add Game</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="icon" type="image/png" href="/Assets/Logo.png">

    <!-- internal css for webpage layout -->
    <style>
        #create-game{
            display: flex;
            justify-content: center;
            align-items: center;
            height: 75vh;
            padding: 20px;
        }

        #create-form{
            background: white;
            width: 100%;
            max-width: 400px;
            max-height: fit-content;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 15px;
        }

        .nav-button{
            background: #2C2F3A;
            height: 10px;
        }

        .nav-bar.bottom{
            height: 20px;
            bottom: 0;
        }

        .element{
            color: black;
            margin: 5px;
            padding: 5px;
        }

        #description{
            min-height: 50px;
        }

        select:has(option[value=""]:checked){
            color: #757575;
        }

        form #add{
            justify-self: left;
            max-width: fit-content;
        }

        form #file{
            background: white;
        }

    </style>
</head>
<body>
    <!-- navigation bar to return to admin panel or logout -->
    <div class="nav-bar">
        <ul class="nav-left"> 
            <img class="page_logo" src="/Assets/Logo.png" alt="">
            <b>Add Game - Admin</b>
        </ul>

        <ul class="nav-right">
            <li><a href="Admin_Panel.html"><label class="nav-button">Back to Admin Panel</label></a></li>
            <li><a href="logout.php"><label class="nav-button">Logout</label></a></li>
        </ul>
    </div>
    <?php
        if (!empty($error_message)){
            echo '<div class="error-message">' . $error_message . '</div>';
    }
        if (!empty($success_message)){
            echo '<div class="success-message">' . $success_message . '</div>';
        }
    ?>
    <div id="create-game">
        <form method = "post" action="Add_Game.php" id="create-form" enctype="multipart/form-data">
            <!-- Game Name input -->
            <input type="text" name="name" placeholder="Game Name" class="element" required>
            <!-- Platform dropdown -->
            <select name="platform" id="platform" class="element" required>
                <option class="element" value="" disabled selected hidden>Select Platform</option>
                <option class="element" value='PC'>PC</option>
                <option class="element" value='PS5'>PS5</option>
                <option class="element" value='Xbox'>Xbox</option>
                <option class="element" value="Nintendo Switch">Nintendo Switch</option>
            </select>
            <!-- Price input -->
            <input type= number step="0.01" name="price" id="price" placeholder="Price (£)" class="element" required>
            <!-- Age Rating input -->
             <select name="age_rating" id="age" class="element" required>
            <option class="element" value="" disabled selected hidden>Select Age Rating</option>
                <option class="element" value='8'>8</option>
                <option class="element" value='13'>13</option>
                <option class="element" value='16'>16</option>
                <option class="element" value='18+'>18+</option>
            </select>
            <!-- File upload input -->
            <input type="file" name="image" id="file" accept="image/*" class="element" required>
            <!-- Description textarea -->
            <textarea name="description" id="description" placeholder="Description" class="element" required></textarea>
            <!-- Submit button -->
            <input type="hidden" name="submitted" value="true"/>
            <button type="submit" id="add" class="element">Add Game</button>
        </form>
    </div>

      <!-- Bottom bar -->
  <div class="nav-bar bottom"></div>

</body>


</html>