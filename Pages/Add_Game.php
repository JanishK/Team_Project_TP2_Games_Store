<?php
$error_message = '';
$success_message = '';

session_start();

$is_admin = $_SESSION['is_admin'] ?? false;

if($is_admin !== 1){
    header("Location: Home_Page.html");
    exit();
}

if(!isset($_SESSION['username'])){
    header("Location: Login_Page.php");
    exit();
}

require_once('connectdb.php');
require_once('themes.php');

if (isset($_POST['submitted'])){

    $name = $_POST['name'] ?? false;
    $price = $_POST['price'] ?? false;
    $description = $_POST['description'] ?? false;
    $platform = $_POST['platform'] ?? false;
    $age_rating = $_POST['age_rating'] ?? false;

    $platforms = ['PC', 'Playstation', 'Xbox', 'Nintendo Switch'];
    $age_ratings = ['8', '13', '16', '18+'];

    if(!in_array($platform, $platforms)){ $platform = false; }
    if(!in_array($age_rating, $age_ratings)){ $age_rating = false; }

    $image = !empty($_FILES['image']['name']) ? $_FILES['image']['name'] : false;

    if($image){
        $target_dir = "images/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);

        $allowed_ext = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $error_message = "Invalid image type. Allowed: jpg, jpeg, png, gif.";
        } else {
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        }
    }

    if (!$name){
        $error_message = "Please enter a valid Game Name!";
    }
    elseif(!$platform){
        $error_message = "Please select a valid Platform!";
    }
    elseif(!$price){
        $error_message = "Please enter a valid Price!";
    }
    elseif(!$age_rating){
        $error_message = "Please enter a valid Age Rating!";
    }
    elseif(!$description){
        $error_message = "Please enter a valid Description!";
    }
    elseif(!$image){
        $error_message = "Please upload a valid Image!";
    }
    else{

        try{
            $stat=$db->prepare("INSERT INTO games (name, description, platform, price, image, age_restriction)
                                VALUES (?, ?, ?, ?, ?, ?)");

            $stat->execute([$name,$description,$platform,$price,$image,$age_rating]);

            $success_message = "Game $name has been added successfully.";

        }catch (PDOException $ex){
            $error_message = "Database error: ".$ex->getMessage();
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
    <link rel="stylesheet" href="../CSS/add_game.css">
    <link rel="icon" type="image/png" href="../Assets/Logo.png">
</head>

<body class="<?php echo $themeClass; ?>">

    <?php require_once __DIR__ . '/components/navbar.php'; ?>



<?php
if (!empty($error_message)){
    echo '<div class="error-message">' . $error_message . '</div>';
}

if (!empty($success_message)){
    echo '<div class="success-message">' . $success_message . '</div>';
}
?>


<div id="create-game">

<form method="post" action="Add_Game.php" id="create-form" enctype="multipart/form-data">

<input type="text" name="name" placeholder="Game Name" class="element" required>

<select name="platform" id="platform" class="element" required>

<option value="" disabled selected hidden>Select Platform</option>
<option value="PC">PC</option>
<option value="Playstation">Playstation</option>
<option value="Xbox">Xbox</option>
<option value="Nintendo Switch">Nintendo Switch</option>

</select>

<input type="number" step="0.01" name="price" placeholder="Price (£)" class="element" required>

<select name="age_rating" class="element" required>

<option value="" disabled selected hidden>Select Age Rating</option>
<option value="8">8</option>
<option value="13">13</option>
<option value="16">16</option>
<option value="18+">18+</option>

</select>

<input type="file" name="image" accept="image/*" class="element file-upload-button" id="file-upload-button" required>

<textarea name="description" placeholder="Description" class="element" required></textarea>

<input type="hidden" name="submitted" value="true"/>

<button type="submit" id="add" class="element">Add Game</button>

</form>

</div>

<div class="nav-bar bottom"></div>

</body>
</html>