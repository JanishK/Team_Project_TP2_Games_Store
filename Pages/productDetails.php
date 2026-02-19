<?php
session_start();
require_once('connectdb.php');

if (!isset($_GET['id'])) {
    die("No product selected.");
}

$gid = (int)$_GET['id'];

$stmt = $db->prepare("
  SELECT gid, name, description, platform, price, image, age_restriction
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

<div class="product-details-page">

  <img src="<?= $imgPath ?>" width="300">

  <h1><?= htmlspecialchars($game['name']); ?></h1>

  <p><?= htmlspecialchars($game['description']); ?></p>

  <h2><?= $price ?></h2>

  <p><strong>Platform:</strong> <?= htmlspecialchars($game['platform']); ?></p>
  <p><strong>Age Rating:</strong> <?= htmlspecialchars($game['age_restriction']); ?></p>

  <form method="post" action="add_to_cart.php">
      <input type="hidden" name="game_id" value="<?= $game['gid']; ?>">
      <button type="submit">Add To Cart</button>
  </form>

</div>

</body>
</html>
