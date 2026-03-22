<?php
session_start();
require_once('connectdb.php');
$logged_in = isset($_SESSION['username']);
if (!isset($_GET['id'])) {
  die("No product selected.");
}
// Increment view count
$viewStmt = $db->prepare("UPDATE games SET view = view + 1 WHERE gid = ?");
// Execute the update statement with the game ID from the query parameter
$viewStmt->execute([(int) $_GET['id']]);
$gid = (int) $_GET['id'];
$genreStmt = $db->prepare("SELECT g.name FROM genres g JOIN game_genres gg ON g.genre_id = gg.genre_id WHERE gg.game_id = ?");
$genreStmt->execute([$gid]);
$genres = $genreStmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db->prepare("
  SELECT gid, name, description, platform, price, image, age_restriction, view, discount
  FROM games
  WHERE gid = ?
");
$stmt->execute([$gid]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
  die("Product not found.");
}
$reviewsStmt = $db->prepare("SELECT rating, comment FROM reviews WHERE game_id = ?");
$reviewsStmt->execute([$gid]);
$reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['submitted'])) {
  if (!$logged_in) {
    header("Location: Login_Page.php");
    exit();
  }
  $rating = $_POST['rating'] ?? null;
  $comment = $_POST['comment'] ?? '';
  $user_id = $_SESSION['user_id'] ?? null;

  if ($rating && $user_id) {
    $insertStmt = $db->prepare("INSERT INTO reviews (game_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $insertStmt->execute([$gid, $user_id, $rating, $comment]);
    header("Location: productDetails.php?id=" . $gid);
    exit();
  } else {
    echo "Please provide a rating.";
  }
}

$baseUrl = "/Team_Project_TP2_Games_Store/Assets/Game_Images/";

$placeholder = $baseUrl . "PlacerHolder.jpeg";

$filename = trim((string) $game['image']);
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
  <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
  <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
  <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>

<body class="<?php echo $themeClass; ?>">
  <!-- NAVIGATION BAR -->
  <?php require_once __DIR__ . '/components/navbar.php'; ?>
  <div class="product-details-page">

    <img src="<?= $imgPath ?>" width="300">

    <h1><?= htmlspecialchars($game['name']); ?></h1>

    <p><?= htmlspecialchars($game['description']); ?></p>
    <p><strong>Genre:</strong> <?= htmlspecialchars(implode(', ', array_column($genres, 'name'))); ?></p>
    <p><strong>Views:</strong> <?= htmlspecialchars((string) $game['view']); ?></p>
    <?php
    $discount = $game['discount'];

    if ($discount > 0) {
      $discountedPrice = $game['price'] * (1 - $discount / 100);
      $priceLabel = "£" . number_format($discountedPrice, 2) .
        " (was £" . number_format((float) $game['price'], 2) . ")";
    } else {
      $priceLabel = "£" . number_format((float) $game['price'], 2);
    }
    ?>
    <p><strong>Price:</strong> <?= htmlspecialchars($priceLabel); ?></p>
    <p><strong>Platform:</strong> <?= htmlspecialchars($game['platform']); ?></p>
    <p><strong>Age Rating:</strong> <?= htmlspecialchars($game['age_restriction']); ?></p>

    <form method="post" action="add_to_cart.php">
      <input type="hidden" name="game_id" value="<?= $game['gid']; ?>">
      <button type="submit">Add To Cart</button>
      <button type="submit">Buy Now</button>
    </form>
    <div id="reviews-section">
      <h2>Reviews</h2>
      <?php if (empty($reviews)): ?>
        <p>No reviews yet.</p>
      <?php else: ?>
        <?php foreach ($reviews as $review): ?>
          <div class="review">
            <label>Rating:</label>
            <div class="star-rating">
              <?php for ($i = 5; $i >= 1; $i--): ?>
            <span class="<?= $i <= $review['rating'] ? 'filled' : 'empty' ?>">★</span>
              <?php endfor; ?>
            </div>
            <p><?= nl2br(htmlspecialchars($review['comment'])); ?></p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
      <div id="review-form">
        <form action="submit_review.php" method="post">
          <input type="hidden" name="game_id" value="<?= $game['gid']; ?>">
          <div class="star-rating">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
              <label for="star<?= $i ?>">★</label>
            <?php endfor; ?>
          </div>
          <label for="comment">Comment:</label>
          <textarea id="comment" name="comment"></textarea>
          <button type="submit" name="submitted">Submit Review</button>
        </form>
      </div>
    </div>
  </div>
</body>

</html>