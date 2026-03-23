<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');
require_once('themes.php');

$logged_in = isset($_SESSION['uid']) || isset($_SESSION['username']);

/* ---- Fetch all games ---- */
$stmt = $db->prepare("
    SELECT gid, name, description, platform, price, image, age_restriction, discount
    FROM games ORDER BY gid DESC
");
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

$platformMap = [
    'Playstation'     => 'PlayStation',
    'Nintendo Switch' => 'Nintendo',
    'Xbox'            => 'Xbox',
    'PC'              => 'PC',
];

function editionTag(string $age): string {
    return ($age === '18+') ? 'ADULT EDITION' : 'STANDARD EDITION';
}

function js($v): string {
    return json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/productPage.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<!-- PAGE HEADER -->
<div class="page-name">
    <h1>Products</h1>
    <p>Explore our collection of games across all platforms.</p>
</div>

<div class="products-page-wrapper">

    <!-- FILTER SIDEBAR -->
    <aside class="filter-bar">
        <h2>Filter</h2>

        <label for="searchInput">Search</label>
        <input type="text" id="searchInput" placeholder="Search games…" oninput="applyFilters()">

        <label for="platformSelect">Platform</label>
        <select id="platformSelect" onchange="applyFilters()">
            <option value="">All Platforms</option>
            <option>PlayStation</option>
            <option>Xbox</option>
            <option>PC</option>
            <option>Nintendo</option>
        </select>

        <label for="ageSelect">Age Rating</label>
        <select id="ageSelect" onchange="applyFilters()">
            <option value="">All Ratings</option>
            <option>8</option>
            <option>13</option>
            <option>16</option>
            <option>18+</option>
        </select>

        <button type="button" onclick="applyFilters()">Apply Filters</button>
        <button type="button" onclick="resetFilters()">Reset</button>
    </aside>

    <!-- PRODUCT GRID -->
    <section class="main-content-wrapper" id="productGrid">
        <?php foreach ($games as $g):
            $platformForFilter = $platformMap[$g['platform']] ?? $g['platform'];
            $discount          = (int)$g['discount'];

            if ($discount > 0) {
                $discountedPrice = $g['price'] * (1 - $discount / 100);
                $priceLabel = "£" . number_format($discountedPrice, 2)
                            . " (was £" . number_format((float)$g['price'], 2) . ")";
            } else {
                $priceLabel = "£" . number_format((float)$g['price'], 2);
            }

            $baseUrl     = "/Team_Project_TP2_Games_Store/Assets/Game_Images/";
            $placeholder = $baseUrl . "PlacerHolder.jpeg";
            $filename    = trim((string)($g['image'] ?? ''));
            $fsPath      = __DIR__ . "/../Assets/Game_Images/" . $filename;
            $imgPath     = ($filename !== '' && is_file($fsPath))
                ? $baseUrl . rawurlencode($filename)
                : $placeholder;

            $dataName     = htmlspecialchars($g['name'],           ENT_QUOTES);
            $dataPlatform = htmlspecialchars($platformForFilter,   ENT_QUOTES);
            $dataAge      = htmlspecialchars($g['age_restriction'], ENT_QUOTES);

            $jsTitle = js($g['name']);
            $jsDesc  = js($g['description']);
            $jsPlat  = js($platformForFilter);
            $jsRate  = js($g['age_restriction']);
            $jsImg   = js($imgPath);
            $jsPrice = js($priceLabel);
            $jsId    = (int)$g['gid'];
        ?>
        <div class="product"
             data-name="<?= $dataName ?>"
             data-platform="<?= $dataPlatform ?>"
             data-age="<?= $dataAge ?>">

            <p><?= htmlspecialchars(editionTag($g['age_restriction']), ENT_QUOTES) ?></p>

            <img src="<?= htmlspecialchars($imgPath, ENT_QUOTES) ?>"
                 alt="<?= $dataName ?>"
                 onerror="this.onerror=null;this.src='<?= htmlspecialchars($placeholder, ENT_QUOTES) ?>';">

            <h3><?= htmlspecialchars(strtoupper($g['name']), ENT_QUOTES) ?></h3>
            <p><?= htmlspecialchars($priceLabel, ENT_QUOTES) ?></p>

            <a href="productDetails.php?id=<?= $jsId ?>" class="cta-button secondary-cta"
               style="margin-bottom:8px;">
                View Details
            </a>

            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="game_id" value="<?= $jsId ?>">
                <input type="hidden" name="redirect" value="stay">
                <button type="submit">Add to Cart</button>
            </form>
        </div>
        <?php endforeach; ?>
    </section>

</div>

<!-- PRODUCT MODAL -->
<div id="productModal" class="product-modal" style="display:none;">
    <div id="modalContent" class="product-modal-content">

        <button id="product-close-btn" type="button" onclick="closeProduct()">✕ Close</button>

        <h1 id="modalTitle"></h1>
        <img id="modalImage" src="" alt="Game cover">
        <p id="modalDescription"></p>
        <h2 id="modalPrice"></h2>
        <p><strong>Platform:</strong> <span id="modalPlatform"></span></p>
        <p><strong>Age Rating:</strong> <span id="modalRating"></span></p>

        <?php if ($logged_in): ?>
            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="game_id"  id="modalGameIdAdd">
                <input type="hidden" name="redirect" value="stay">
                <button type="submit">Add to Cart</button>
            </form>
            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="game_id"  id="modalGameIdBuy">
                <input type="hidden" name="redirect" value="basket">
                <button type="submit">Buy Now</button>
            </form>

            <hr style="border-color:var(--border);margin:18px 0;">
            <h3>Leave a Review</h3>
            <form action="submit_review.php" method="post">
                <input type="hidden" name="game_id" id="modalGameIdReview">
                <label>Rating (1–5):</label>
                <input type="number" name="rating" min="1" max="5" required>
                <label>Comment:</label>
                <textarea name="comment" rows="3" required></textarea>
                <button type="submit">Submit Review</button>
            </form>
        <?php else: ?>
            <p>Please <a href="Login_Page.php">sign in</a> to add to cart or leave a review.</p>
        <?php endif; ?>

    </div>
</div>

<script>
function applyFilters() {
    const search   = document.getElementById('searchInput').value.toLowerCase();
    const platform = document.getElementById('platformSelect').value;
    const age      = document.getElementById('ageSelect').value;

    document.querySelectorAll('#productGrid .product').forEach(card => {
        const name    = (card.dataset.name     || '').toLowerCase();
        const plat    = card.dataset.platform  || '';
        const ageRat  = card.dataset.age       || '';

        const ok = (!search   || name.includes(search))
                && (!platform || plat   === platform)
                && (!age      || ageRat === age);

        card.style.display = ok ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('searchInput').value   = '';
    document.getElementById('platformSelect').value = '';
    document.getElementById('ageSelect').value      = '';
    applyFilters();
}

function closeProduct() {
    document.getElementById('productModal').style.display = 'none';
    document.body.style.overflow = '';
}

document.getElementById('productModal').addEventListener('click', function(e) {
    if (e.target === this) closeProduct();
});
</script>

</body>
</html>