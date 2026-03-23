<?php
session_start();
require_once('connectdb.php');
require_once('themes.php');

function editionTag(string $age): string {
    return ($age === '18+') ? 'ADULT EDITION' : 'STANDARD EDITION';
}

function js($value): string {
    return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function imagePathFor(array $game, string $dir): string {
    $baseUrl     = "/Team_Project_TP2_Games_Store/Assets/Game_Images/";
    $placeholder = $baseUrl . "PlacerHolder.jpeg";
    $filename    = trim((string)($game['image'] ?? ''));
    $fsPath      = $dir . $filename;
    return ($filename !== '' && is_file($fsPath))
        ? $baseUrl . rawurlencode($filename)
        : $placeholder;
}

function priceLabel(array $game): string {
    $discount = (int)$game['discount'];
    if ($discount > 0) {
        $disc = $game['price'] * (1 - $discount / 100);
        return '£' . number_format($disc, 2) . ' <s style="opacity:.5;font-size:13px;">£' . number_format((float)$game['price'], 2) . '</s>';
    }
    return '£' . number_format((float)$game['price'], 2);
}

/* ---- Flash message from order ---- */
$orderSuccess = $_SESSION['order_success'] ?? '';
$orderId      = $_SESSION['last_order_id'] ?? null;
unset($_SESSION['order_success'], $_SESSION['last_order_id']);

/* ---- Trending games ---- */
try {
    $stmt = $db->query("
        SELECT gid, name, description, age_restriction, platform, price, discount, image
        FROM games ORDER BY view DESC LIMIT 8
    ");
    $trendingGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    $trendingGames = [];
}

/* ---- Deals ---- */
try {
    $stmt = $db->query("
        SELECT gid, name, description, age_restriction, platform, price, image, discount
        FROM games WHERE discount > 0 ORDER BY discount DESC LIMIT 8
    ");
    $deals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    $deals = [];
}

$imgDir = __DIR__ . "/../Assets/Game_Images/";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreByte | Home</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
    <style>
        /* ---- Order success toast ---- */
        .toast-success {
            position: fixed;
            bottom: 28px;
            right: 24px;
            z-index: 999999;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-radius: 16px;
            background: rgba(80, 200, 120, 0.18);
            border: 1px solid rgba(80, 200, 120, 0.45);
            box-shadow: 0 20px 50px rgba(0,0,0,0.45);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            max-width: 380px;
            animation: toastIn 0.35s cubic-bezier(0.34,1.56,0.64,1) both;
        }
        .toast-success__icon { font-size: 22px; flex-shrink: 0; }
        .toast-success__text { font-size: 14px; font-weight: 700; color: #7de87a; line-height: 1.45; }
        .toast-success__close {
            margin-left: auto;
            background: none;
            border: none;
            color: rgba(125,232,122,0.65);
            font-size: 18px;
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 6px;
            flex-shrink: 0;
        }
        @keyframes toastIn {
            from { opacity:0; transform: translateY(20px) scale(0.95); }
            to   { opacity:1; transform: translateY(0)    scale(1); }
        }
        @keyframes toastOut {
            to { opacity:0; transform: translateY(20px) scale(0.95); }
        }
        .toast-success.hiding { animation: toastOut 0.25s ease forwards; }
    </style>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<?php if (!empty($orderSuccess)): ?>
<div class="toast-success" id="orderToast" role="alert">
    <span class="toast-success__icon">✅</span>
    <span class="toast-success__text"><?= htmlspecialchars($orderSuccess) ?></span>
    <button class="toast-success__close" onclick="dismissToast()" aria-label="Dismiss">×</button>
</div>
<script>
    function dismissToast() {
        const t = document.getElementById('orderToast');
        t.classList.add('hiding');
        setTimeout(() => t.remove(), 260);
    }
    setTimeout(dismissToast, 6000);
</script>
<?php endif; ?>

<!-- HERO -->
<section class="hero-banner">
    <div class="hero-content">
        <h1>Welcome to CoreByte</h1>
        <p>Your one-stop store for digital games, deals &amp; instant downloads.</p>
        <button class="hero-btn" onclick="window.location='/Team_Project_TP2_Games_Store/Pages/Products_Page.php'">
            Shop Now
        </button>
    </div>
</section>

<!-- CATEGORIES -->
<section class="categories-section">
    <h2 class="section-title">Browse by Category</h2>
    <div class="categories-container">
        <button class="category-card">🎮 Action</button>
        <button class="category-card">🗺️ Adventure</button>
        <button class="category-card">⚔️ RPG</button>
        <button class="category-card">🏎️ Racing</button>
        <button class="category-card">⚽ Sports</button>
        <button class="category-card">👻 Horror</button>
    </div>
</section>

<!-- TRENDING GAMES -->
<section class="trending-section">
    <h2 class="section-title">🔥 Trending Now</h2>
    <div class="main-content-wrapper">
        <?php foreach ($trendingGames as $game):
            $imgPath      = imagePathFor($game, $imgDir);
            $placeholder  = "/Team_Project_TP2_Games_Store/Assets/Game_Images/PlacerHolder.jpeg";
            $dataName     = htmlspecialchars($game['name'], ENT_QUOTES);
            $dataPlatform = htmlspecialchars($game['platform'] ?? '', ENT_QUOTES);
            $dataAge      = htmlspecialchars($game['age_restriction'] ?? '', ENT_QUOTES);
        ?>
        <div class="product"
             data-name="<?= $dataName ?>"
             data-platform="<?= $dataPlatform ?>"
             data-age="<?= $dataAge ?>">

            <p><?= htmlspecialchars(editionTag($game['age_restriction']), ENT_QUOTES) ?></p>

            <a href="productDetails.php?id=<?= (int)$game['gid'] ?>">
                <img src="<?= htmlspecialchars($imgPath, ENT_QUOTES) ?>"
                     alt="<?= $dataName ?>"
                     onerror="this.onerror=null;this.src='<?= htmlspecialchars($placeholder, ENT_QUOTES) ?>';">
            </a>

            <h3><?= htmlspecialchars(strtoupper($game['name']), ENT_QUOTES) ?></h3>
            <p><?= priceLabel($game) ?></p>

            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="game_id" value="<?= (int)$game['gid'] ?>">
                <button type="submit">Add to Cart</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- DEALS -->
<?php if (!empty($deals)): ?>
<section class="trending-section">
    <h2 class="section-title">🏷️ Deals of the Week</h2>
    <div class="main-content-wrapper">
        <?php foreach ($deals as $game):
            $imgPath      = imagePathFor($game, $imgDir);
            $placeholder  = "/Team_Project_TP2_Games_Store/Assets/Game_Images/PlacerHolder.jpeg";
            $dataName     = htmlspecialchars($game['name'], ENT_QUOTES);
            $dataPlatform = htmlspecialchars($game['platform'] ?? '', ENT_QUOTES);
            $dataAge      = htmlspecialchars($game['age_restriction'] ?? '', ENT_QUOTES);
        ?>
        <div class="product"
             data-name="<?= $dataName ?>"
             data-platform="<?= $dataPlatform ?>"
             data-age="<?= $dataAge ?>">

            <p><?= htmlspecialchars(editionTag($game['age_restriction']), ENT_QUOTES) ?></p>

            <a href="productDetails.php?id=<?= (int)$game['gid'] ?>">
                <img src="<?= htmlspecialchars($imgPath, ENT_QUOTES) ?>"
                     alt="<?= $dataName ?>"
                     onerror="this.onerror=null;this.src='<?= htmlspecialchars($placeholder, ENT_QUOTES) ?>';">
            </a>

            <h3><?= htmlspecialchars(strtoupper($game['name']), ENT_QUOTES) ?></h3>
            <p><?= priceLabel($game) ?></p>

            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="game_id" value="<?= (int)$game['gid'] ?>">
                <button type="submit">Add to Cart</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- PLATFORM LOGOS -->
<section class="platform-logos">
    <h2 class="section-title">Available On</h2>
    <div class="logo-row">
        <img src="/Team_Project_TP2_Games_Store/Assets/ICONS/PlayStation_logo.png" alt="PlayStation">
        <img src="/Team_Project_TP2_Games_Store/Assets/ICONS/Xbox_one_logo.png" alt="Xbox">
        <img src="/Team_Project_TP2_Games_Store/Assets/ICONS/Nintendo_logo.png" alt="Nintendo">
        <img src="/Team_Project_TP2_Games_Store/Assets/ICONS/PC_LOGO_2.png" alt="PC">
    </div>
</section>

<!-- NEWSLETTER -->
<section class="newsletter-section">
    <h2>Join the CoreByte Community</h2>
    <p>Get exclusive offers, updates and early access to discounts.</p>
    <div class="newsletter-box">
        <input type="email" placeholder="Enter your email">
        <button type="button">Subscribe</button>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-box">
        <div class="footer-header">
            <h3>CoreByte</h3>
            <p>Your go-to store for digital games and instant downloads.</p>
        </div>
        <div class="footer-columns">
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="home_Page.php">Home</a></li>
                    <li><a href="Products_Page.php">Products</a></li>
                    <li><a href="aboutUs_Page.php">About</a></li>
                    <li><a href="contactUs_Page.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Support</h3>
                <ul>
                    <li><a href="contactUs_Page.php">FAQ</a></li>
                    <li><a href="contactUs_Page.php">Customer Service</a></li>
                    <li><a href="contactUs_Page.php">Refund Policy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <p>Instagram / TikTok / YouTube</p>
                <p style="font-size:13px;opacity:.7;">@CoreByteStore</p>
            </div>
        </div>
        <p class="copyright">© 2025 CoreByte. All rights reserved.</p>
    </div>
</footer>

</body>
</html>