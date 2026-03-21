<?php
session_start();

$error_message = '';
$success_message = '';



// User login check
if (!isset($_SESSION['username'])) {
    header("Location: Login_Page.php");
    exit();
}

require_once('connectdb.php');
require_once('themes.php');

// Get game ID from URL
$gid = $_GET['gid'] ?? null;


// Fetch existing game
$stat = $db->prepare("SELECT * FROM games WHERE gid = ?");
$stat->execute([$gid]);
$game = $stat->fetch();

if (!$game) {
    die("Game not found.");
}

// Handle form submission
if (isset($_POST['submitted'])) {

    $name = $_POST['name'] ?? false;
    $price = $_POST['price'] ?? false;
    $description = $_POST['description'] ?? false;
    $platform = $_POST['platform'] ?? false;
    $age_rating = $_POST['age_rating'] ?? false;
    $discount = $_POST['discount'] ?? 0;

    $platforms = ['PC', 'Playstation', 'Xbox', 'Nintendo Switch'];
    $age_ratings = ['8', '13', '16', '18+'];

    if (!in_array($platform, $platforms)) { $platform = false; }
    if (!in_array($age_rating, $age_ratings)) { $age_rating = false; }

    // Default: keep old image
    $image = $game['image'];

    // If new image uploaded
    if (!empty($_FILES['image']['name'])) {

        $target_dir = __DIR__ . "/../Assets/Game_Images/";
        $image_name = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        $allowed_ext = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $error_message = "Invalid image type. Allowed: jpg, jpeg, png, gif.";
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $image_name;
            } else {
                $error_message = "Error uploading image.";
            }
        }
    }

    // Validation
    if (!$name) {
        $error_message = "Please enter a valid Game Name!";
    }
    elseif (!$platform) {
        $error_message = "Please select a valid Platform!";
    }
    elseif (!$price) {
        $error_message = "Please enter a valid Price!";
    }
    elseif (!$age_rating) {
        $error_message = "Please enter a valid Age Rating!";
    }
    elseif (!$description) {
        $error_message = "Please enter a valid Description!";
    }
    elseif ($price < 0) {
        $error_message = "Price cannot be negative!";
    }
    elseif ($discount < 0 || $discount > 100) {
        $error_message = "Invalid discount value. Please enter between 0 and 100.";
    }
    else {
        try {
            $stat = $db->prepare("
                UPDATE games 
                SET name=?, description=?, platform=?, price=?, image=?, age_restriction=?, discount=? 
                WHERE gid=?
            ");

            $stat->execute([
                $name,
                $description,
                $platform,
                $price,
                $image,
                $age_rating,
                $discount,
                $gid
            ]);

            $success_message = "Game updated successfully.";

            // Refresh game data after update
            $stat = $db->prepare("SELECT * FROM games WHERE gid = ?");
            $stat->execute([$gid]);
            $game = $stat->fetch();

        } catch (PDOException $ex) {
            $error_message = "Database error: " . $ex->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Game</title>

<link rel="stylesheet" href="../CSS/style.css">
<link rel="stylesheet" href="../CSS/add_game.css">
<link rel="icon" type="image/png" href="../Assets/Logo.png">
<script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
<link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
<script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>

<body class="<?php echo $themeClass; ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<?php
if (!empty($error_message)) {
    echo '<div class="error-message">' . $error_message . '</div>';
}

if (!empty($success_message)) {
    echo '<div class="success-message">' . $success_message . '</div>';
}
?>

<div id="create-game">

<form method="post" action="" enctype="multipart/form-data" id="create-form">

<input type="text" name="name" value="<?php echo htmlspecialchars($game['name']); ?>" placeholder="Game Name" class="element" required>

<select name="platform" class="element" required>
<option disabled>Select Platform</option>
<option value="PC" <?php if($game['platform']=="PC") echo 'selected'; ?>>PC</option>
<option value="Playstation" <?php if($game['platform']=="Playstation") echo 'selected'; ?>>Playstation</option>
<option value="Xbox" <?php if($game['platform']=="Xbox") echo 'selected'; ?>>Xbox</option>
<option value="Nintendo Switch" <?php if($game['platform']=="Nintendo Switch") echo 'selected'; ?>>Nintendo Switch</option>
</select>

<input type="number" step="0.01" name="price" value="<?php echo $game['price']; ?>" placeholder="Price (£)" class="element" required>

<select name="age_rating" class="element" required>
<option disabled>Select Age Rating</option>
<option value="8" <?php if($game['age_restriction']=="8") echo 'selected'; ?>>8</option>
<option value="13" <?php if($game['age_restriction']=="13") echo 'selected'; ?>>13</option>
<option value="16" <?php if($game['age_restriction']=="16") echo 'selected'; ?>>16</option>
<option value="18+" <?php if($game['age_restriction']=="18+") echo 'selected'; ?>>18+</option>
</select>

<!-- Current Image -->
<p style="color:white;">Current Image:</p>
<img src="../Assets/Game_Images/<?php echo $game['image']; ?>" width="120">

<input type="file" name="image" accept="image/*" class="element">

<textarea name="description" class="element" required><?php echo htmlspecialchars($game['description']); ?></textarea>

<input type="number" name="discount" value="<?php echo $game['discount']; ?>" placeholder="Discount (%)" class="element" min="0" max="100">

<input type="hidden" name="submitted" value="true">

<button type="submit" class="element" id="add">Update Game</button>

</form>

</div>

<div class="nav-bar bottom"></div>

</body>
</html>