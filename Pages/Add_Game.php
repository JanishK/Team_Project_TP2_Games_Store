<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');
require_once('themes.php');

/* ---- Auth ---- */
if (!isset($_SESSION['username'])) {
    header("Location: Login_Page.php");
    exit();
}

$is_admin = (int)($_SESSION['is_admin'] ?? 0);
if ($is_admin !== 1) {
    header("Location: home_Page.php");
    exit();
}

$error_message   = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitted'])) {

    $name        = trim($_POST['name']        ?? '');
    $price       = $_POST['price']            ?? '';
    $description = trim($_POST['description'] ?? '');
    $platform    = $_POST['platform']         ?? '';
    $age_rating  = $_POST['age_rating']       ?? '';
    $discount    = (int)($_POST['discount']   ?? 0);

    $allowed_platforms = ['PC', 'Playstation', 'Xbox', 'Nintendo Switch'];
    $allowed_ages      = ['8', '13', '16', '18+'];

    if (!in_array($platform,   $allowed_platforms, true)) $platform  = '';
    if (!in_array($age_rating, $allowed_ages,      true)) $age_rating = '';

    /* Image upload */
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $target_dir  = __DIR__ . "/../Assets/Game_Images/";
        $image_name  = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;
        $ext         = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed_ext, true)) {
            $error_message = "Invalid image type. Allowed: jpg, jpeg, png, gif, webp.";
        } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $error_message = "Error uploading image. Please try again.";
        } else {
            $image = $image_name;
        }
    }

    /* Validation */
    if (!$error_message) {
        if (!$name)        $error_message = "Please enter a game name.";
        elseif (!$platform)   $error_message = "Please select a platform.";
        elseif ($price === '' || !is_numeric($price) || (float)$price < 0)
                              $error_message = "Please enter a valid price.";
        elseif (!$age_rating) $error_message = "Please select an age rating.";
        elseif (!$description)$error_message = "Please enter a description.";
        elseif (!$image)      $error_message = "Please upload a game image.";
        elseif ($discount < 0 || $discount > 100)
                              $error_message = "Discount must be between 0 and 100.";
    }

    if (!$error_message) {
        try {
            $stat = $db->prepare("
                INSERT INTO games (name, description, platform, price, image, age_restriction, discount)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stat->execute([$name, $description, $platform, (float)$price, $image, $age_rating, $discount]);
            $success_message = "\"$name\" has been added successfully!";
        } catch (PDOException $ex) {
            $error_message = "Database error. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Game | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/add_game.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<?php if (!empty($error_message)): ?>
    <div class="error-message" style="max-width:680px;margin:16px auto;">
        <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>
<?php if (!empty($success_message)): ?>
    <div class="success-message" style="max-width:680px;margin:16px auto;">
        <?= htmlspecialchars($success_message) ?>
    </div>
<?php endif; ?>

<div id="create-game">
    <form method="post" action="Add_Game.php" id="create-form" enctype="multipart/form-data">

        <input type="text"  name="name"  placeholder="Game Name"  class="element" required
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

        <select name="platform" class="element" required>
            <option value="" disabled selected>Select Platform</option>
            <option value="PC"             <?= ($_POST['platform'] ?? '') === 'PC'             ? 'selected' : '' ?>>PC</option>
            <option value="Playstation"    <?= ($_POST['platform'] ?? '') === 'Playstation'    ? 'selected' : '' ?>>PlayStation</option>
            <option value="Xbox"           <?= ($_POST['platform'] ?? '') === 'Xbox'           ? 'selected' : '' ?>>Xbox</option>
            <option value="Nintendo Switch"<?= ($_POST['platform'] ?? '') === 'Nintendo Switch'? 'selected' : '' ?>>Nintendo Switch</option>
        </select>

        <input type="number" step="0.01" min="0" name="price" placeholder="Price (£)"
               class="element" required
               value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">

        <select name="age_rating" class="element" required>
            <option value="" disabled selected>Select Age Rating</option>
            <option value="8"   <?= ($_POST['age_rating'] ?? '') === '8'   ? 'selected' : '' ?>>8</option>
            <option value="13"  <?= ($_POST['age_rating'] ?? '') === '13'  ? 'selected' : '' ?>>13</option>
            <option value="16"  <?= ($_POST['age_rating'] ?? '') === '16'  ? 'selected' : '' ?>>16</option>
            <option value="18+" <?= ($_POST['age_rating'] ?? '') === '18+' ? 'selected' : '' ?>>18+</option>
        </select>

        <input type="file" name="image" accept="image/*" class="element" required>

        <textarea name="description" placeholder="Game description…" class="element"
                  required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

        <input type="number" name="discount" placeholder="Discount (%)" class="element"
               min="0" max="100" value="<?= (int)($_POST['discount'] ?? 0) ?>">

        <input type="hidden" name="submitted" value="true">
        <button type="submit" id="add" class="element">Add Game</button>

    </form>
</div>

</body>
</html>