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

$gid = isset($_GET['gid']) ? (int)$_GET['gid'] : 0;
if (!$gid) {
    header("Location: Admin_Panel.php");
    exit();
}

/* ---- Fetch game ---- */
$stat = $db->prepare("SELECT * FROM games WHERE gid = ? LIMIT 1");
$stat->execute([$gid]);
$game = $stat->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    header("Location: Admin_Panel.php");
    exit();
}

$error_message   = '';
$success_message = '';

/* ---- Handle form submission ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitted'])) {

    $name        = trim($_POST['name']        ?? '');
    $price       = $_POST['price']            ?? '';
    $description = trim($_POST['description'] ?? '');
    $platform    = $_POST['platform']         ?? '';
    $age_rating  = $_POST['age_rating']       ?? '';
    $discount    = (int)($_POST['discount']   ?? 0);

    $allowed_platforms = ['PC', 'Playstation', 'Xbox', 'Nintendo Switch'];
    $allowed_ages      = ['8', '13', '16', '18+'];

    if (!in_array($platform,   $allowed_platforms, true)) $platform   = '';
    if (!in_array($age_rating, $allowed_ages,      true)) $age_rating = '';

    /* Keep existing image unless a new one is uploaded */
    $image = $game['image'];

    if (!empty($_FILES['image']['name'])) {
        $target_dir  = __DIR__ . "/../Assets/Game_Images/";
        $image_name  = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;
        $ext         = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed_ext, true)) {
            $error_message = "Invalid image type. Allowed: jpg, jpeg, png, gif, webp.";
        } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $error_message = "Error uploading image.";
        } else {
            $image = $image_name;
        }
    }

    /* Validation */
    if (!$error_message) {
        if (!$name)           $error_message = "Please enter a game name.";
        elseif (!$platform)   $error_message = "Please select a platform.";
        elseif ($price === '' || !is_numeric($price) || (float)$price < 0)
                              $error_message = "Please enter a valid price.";
        elseif (!$age_rating) $error_message = "Please select an age rating.";
        elseif (!$description)$error_message = "Please enter a description.";
        elseif ($discount < 0 || $discount > 100)
                              $error_message = "Discount must be between 0 and 100.";
    }

    if (!$error_message) {
        try {
            $stat = $db->prepare("
                UPDATE games
                SET name=?, description=?, platform=?, price=?, image=?, age_restriction=?, discount=?
                WHERE gid=?
            ");
            $stat->execute([$name, $description, $platform, (float)$price, $image, $age_rating, $discount, $gid]);
            $success_message = "\"$name\" updated successfully.";

            /* Refresh game data */
            $stat = $db->prepare("SELECT * FROM games WHERE gid = ? LIMIT 1");
            $stat->execute([$gid]);
            $game = $stat->fetch(PDO::FETCH_ASSOC);

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
    <title>Edit Game | CoreByte</title>
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
    <form method="post" action="edit_game.php?gid=<?= $gid ?>"
          id="create-form" enctype="multipart/form-data">

        <input type="text" name="name" placeholder="Game Name" class="element" required
               value="<?= htmlspecialchars($game['name']) ?>">

        <select name="platform" class="element" required>
            <option disabled>Select Platform</option>
            <?php foreach (['PC', 'Playstation', 'Xbox', 'Nintendo Switch'] as $p): ?>
                <option value="<?= $p ?>" <?= $game['platform'] === $p ? 'selected' : '' ?>><?= $p ?></option>
            <?php endforeach; ?>
        </select>

        <input type="number" step="0.01" min="0" name="price" placeholder="Price (£)"
               class="element" required
               value="<?= htmlspecialchars((string)$game['price']) ?>">

        <select name="age_rating" class="element" required>
            <option disabled>Select Age Rating</option>
            <?php foreach (['8', '13', '16', '18+'] as $a): ?>
                <option value="<?= $a ?>" <?= $game['age_restriction'] === $a ? 'selected' : '' ?>><?= $a ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Current image preview -->
        <p style="color:var(--text-muted);font-size:13px;font-weight:700;margin-bottom:6px;">
            Current Image:
        </p>
        <img src="/Team_Project_TP2_Games_Store/Assets/Game_Images/<?= htmlspecialchars($game['image']) ?>"
             alt="Current game image"
             style="width:120px;border-radius:10px;border:1px solid var(--border);margin-bottom:10px;">

        <input type="file" name="image" accept="image/*" class="element">

        <textarea name="description" placeholder="Game description…"
                  class="element" required><?= htmlspecialchars($game['description']) ?></textarea>

        <input type="number" name="discount" placeholder="Discount (%)" class="element"
               min="0" max="100" value="<?= (int)$game['discount'] ?>">

        <input type="hidden" name="submitted" value="true">
        <button type="submit" id="add" class="element">Update Game</button>

    </form>
</div>

</body>
</html>