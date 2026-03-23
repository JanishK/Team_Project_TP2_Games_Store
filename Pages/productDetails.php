<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');
require_once('themes.php');

$logged_in = isset($_SESSION['uid']) || isset($_SESSION['username']);

if (!isset($_GET['id'])) {
    header("Location: Products_Page.php");
    exit();
}

$gid = (int)$_GET['id'];

/* ---- Increment view count ---- */
$db->prepare("UPDATE games SET view = view + 1 WHERE gid = ?")->execute([$gid]);

/* ---- Fetch game ---- */
$stmt = $db->prepare("
    SELECT gid, name, description, platform, price, image, age_restriction, view, discount
    FROM games WHERE gid = ?
");
$stmt->execute([$gid]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    header("Location: Products_Page.php");
    exit();
}

/* ---- Genres ---- */
$genreStmt = $db->prepare("
    SELECT g.name FROM genres g
    JOIN game_genres gg ON g.genre_id = gg.genre_id
    WHERE gg.game_id = ?
");
$genreStmt->execute([$gid]);
$genres = $genreStmt->fetchAll(PDO::FETCH_ASSOC);

/* ---- Reviews ---- */
$reviewsStmt = $db->prepare("
    SELECT r.rating, r.comment, u.username
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.uid
    WHERE r.game_id = ?
    ORDER BY r.review_id DESC
");
$reviewsStmt->execute([$gid]);
$reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

/* ---- Image path ---- */
$baseUrl     = "/Team_Project_TP2_Games_Store/Assets/Game_Images/";
$placeholder = $baseUrl . "PlacerHolder.jpeg";
$filename    = trim((string)$game['image']);
$fsPath      = __DIR__ . "/../Assets/Game_Images/" . $filename;
$imgPath     = ($filename !== '' && is_file($fsPath))
    ? $baseUrl . rawurlencode($filename)
    : $placeholder;

/* ---- Price label ---- */
$discount = (int)$game['discount'];
if ($discount > 0) {
    $discounted = $game['price'] * (1 - $discount / 100);
    $priceLabel = "£" . number_format($discounted, 2)
                . " <s style='opacity:.55;font-size:14px;'>£" . number_format((float)$game['price'], 2) . "</s>"
                . " <span class='badge success' style='margin-left:6px;'>-$discount%</span>";
} else {
    $priceLabel = "£" . number_format((float)$game['price'], 2);
}

/* ---- Review flash ---- */
$reviewSuccess = isset($_GET['review_success']);
$reviewError   = isset($_GET['review_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($game['name']) ?> | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/productPage.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<div class="product-details-page">

    <!-- Image -->
    <img src="<?= htmlspecialchars($imgPath) ?>"
         alt="<?= htmlspecialchars($game['name']) ?>"
         onerror="this.onerror=null;this.src='<?= htmlspecialchars($placeholder) ?>';">

    <!-- Info -->
    <div class="product-details-info">

        <h2><?= htmlspecialchars($game['name']) ?></h2>

        <?php if (!empty($genres)): ?>
            <p><strong>Genre:</strong> <?= htmlspecialchars(implode(', ', array_column($genres, 'name'))) ?></p>
        <?php endif; ?>

        <p><?= htmlspecialchars($game['description']) ?></p>

        <p><strong>Platform:</strong> <?= htmlspecialchars($game['platform']) ?></p>
        <p><strong>Age Rating:</strong> <?= htmlspecialchars($game['age_restriction']) ?></p>
        <p><strong>Views:</strong> <?= (int)$game['view'] ?></p>

        <p class="product-price"><strong>Price:</strong> <?= $priceLabel ?></p>

        <?php if ($logged_in): ?>
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
                <form method="post" action="add_to_cart.php">
                    <input type="hidden" name="game_id" value="<?= $gid ?>">
                    <input type="hidden" name="redirect" value="stay">
                    <button type="submit">Add to Cart</button>
                </form>
                <form method="post" action="add_to_cart.php">
                    <input type="hidden" name="game_id" value="<?= $gid ?>">
                    <input type="hidden" name="redirect" value="basket">
                    <button type="submit">Buy Now</button>
                </form>
            </div>
        <?php else: ?>
            <p><a href="Login_Page.php">Sign in</a> to add this game to your cart.</p>
        <?php endif; ?>

        <!-- REVIEWS -->
        <div id="reviews-section" style="margin-top:32px;">
            <h2>Reviews</h2>

            <?php if ($reviewSuccess): ?>
                <div class="success-message">Your review has been posted!</div>
            <?php elseif ($reviewError): ?>
                <div class="error-message">Please provide a rating and comment.</div>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
                <p style="color:var(--text-muted);">No reviews yet. Be the first!</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <span class="review-author">
                            <?= htmlspecialchars($review['username'] ?? 'Anonymous') ?>
                        </span>
                        <span class="review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?= $i <= (int)$review['rating'] ? 'star filled' : 'star empty' ?>">★</span>
                            <?php endfor; ?>
                        </span>
                    </div>
                    <p class="review-body"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($logged_in): ?>
            <div id="review-form" style="margin-top:24px;">
                <h3>Leave a Review</h3>
                <form action="submit_review.php" method="post">
                    <input type="hidden" name="game_id" value="<?= $gid ?>">

                    <div class="star-rating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                            <label for="star<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</label>
                        <?php endfor; ?>
                    </div>

                    <label for="comment" style="display:block;margin:12px 0 6px;font-weight:700;color:var(--text-muted);">
                        Comment
                    </label>
                    <textarea id="comment" name="comment" rows="4"
                              placeholder="Share your thoughts…"
                              required style="width:100%;"></textarea>

                    <button type="submit" name="submitted" style="margin-top:12px;">
                        Submit Review
                    </button>
                </form>
            </div>
            <?php else: ?>
                <p style="margin-top:16px;color:var(--text-muted);">
                    <a href="Login_Page.php">Sign in</a> to leave a review.
                </p>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>