<?php
session_start();
require_once('connectdb.php');

if (!isset($_GET['id'])) {
    die("No product selected.");
}
// Increment view count
$viewStmt = $db->prepare("UPDATE games SET view = view + 1 WHERE gid = ?");
// Execute the update statement with the game ID from the query parameter
$viewStmt->execute([(int)$_GET['id']]);
$gid = (int)$_GET['id'];

$stmt = $db->prepare("
  SELECT gid, name, description, platform, price, image, age_restriction, view
  FROM games
  WHERE gid = ?
");
$stmt->execute([$gid]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    die("Product not found.");
}

$baseUrl = "/Team_Project_TP2_Games_Store/Assets/Game_Images/";
$placeholder = $baseUrl . "PlacerHolder.jpeg";

$filename = trim((string)$game['image']);
$fsPath = __DIR__ . "/../Assets/Game_Images/" . $filename;

$imgPath = (is_file($fsPath) && $filename !== "")
  ? $baseUrl . rawurlencode($filename)
  : $placeholder;

$price = "£" . number_format($game['price'], 2);
?>

<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($game['name']); ?></title>
  <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/productPage.css">
</head>

<body>
    <body class="<?php echo $themeClass; ?>">
        <!-- NAVIGATION BAR -->
        <?php require_once __DIR__ . '/components/navbar.php'; ?>
<div class="product-details-page">

  <img src="<?= $imgPath ?>" width="300">

  <h1><?= htmlspecialchars($game['name']); ?></h1>

  <p><?= htmlspecialchars($game['description']); ?></p>
  <p><strong>Views:</strong> <?= htmlspecialchars((string)$game['view']); ?></p>

  <h2><?= $price ?></h2>

  <p><strong>Platform:</strong> <?= htmlspecialchars($game['platform']); ?></p>
  <p><strong>Age Rating:</strong> <?= htmlspecialchars($game['age_restriction']); ?></p>

  <form method="post" action="add_to_cart.php">
      <input type="hidden" name="game_id" value="<?= $game['gid']; ?>">
      <button type="submit">Add To Cart</button>
      <button type="submit">Buy Now</button>
  </form>

</div>

</body>
</html>
