<?php
session_start();
require_once('connectdb.php');

$logged_in = isset($_SESSION['username']);

/* Fetch all games */
$stmt = $db->prepare("
  SELECT gid, name, description, platform, price, image, age_restriction
  FROM games
  ORDER BY gid DESC
");
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Map DB platform -> dropdown label */
$platformMap = [
  'Playstation'      => 'PlayStation',
  'Nintendo Switch'  => 'Nintendo',
  'Xbox'             => 'Xbox',
  'PC'               => 'PC'
];

/* Optional: simple “edition” label from age rating (just a display tag) */
function editionTag($age) {
  return ($age === '18+') ? 'ADULT EDITION' : 'STANDARD EDITION';
}

/* Make a safe JS string */
function js($value) {
  return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CoreByte | Products</title>
  <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css?v=1" />
  <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png" />
  <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
</head>

<body class="<?php echo $themeClass; ?>">

  <!-- NAVIGATION BAR (your cb-nav) -->
  <nav class="cb-nav">
    <div class="cb-nav__container">

      <a class="cb-brand" href="./home_Page.php">
        <img class="cb-brand__logo" src="/Team_Project_TP2_Games_Store/Assets/Logo.png" alt="CoreByte Logo" />
        <span class="cb-brand__text">CoreByte</span>
      </a>

      <ul class="cb-links" id="cbNavLinks">
        <li><a href="./home_Page.php" class="cb-link">Home</a></li>
        <li><a href="./Products_Page.php" class="cb-link is-active">Products</a></li>
        <li><a href="./aboutUs_Page.php" class="cb-link">About</a></li>
      </ul>

      <div class="cb-user">
        <button class="cb-user__btn" type="button" id="cbUserBtn" aria-expanded="false" aria-controls="cbUserMenu">
          <span class="sr-only">Open user menu</span>
          <img class="cb-user__avatar" src="https://flowbite.com/docs/images/people/profile-picture-5.jpg" alt="User photo" />
        </button>

        <div class="cb-user__menu hidden" id="cbUserMenu" role="menu">
          <div class="cb-user__header">
            <span class="cb-user__name">Janish Kandel</span>
            <span class="cb-user__email">JanishK@corebyte.com</span>
          </div>

          <a href="./basket_Page.php" role="menuitem">Basket</a>
          <a href="./registration_page.php" role="menuitem">Account</a>
          <a href="./settingsPage.php" role="menuitem">Settings</a>
          <a href="./contactUs_Page.php" role="menuitem">Support</a>
          <a href="#" role="menuitem">Sign out</a>
        </div>
      </div>

    </div>
  </nav>

  <!-- PAGE HEADER -->
  <div class="page-name" style="padding-top: 90px;">
    <h1>Products</h1>
    <p>Explore our extensive collection of games available for purchase.</p>
  </div>

  <div class="products-page-wrapper">

    <!-- FILTER SIDEBAR -->
    <aside class="filter-bar">
      <h2>Filter</h2>

      <label for="searchInput">Search for your favorite games</label>
      <input type="text" id="searchInput" placeholder="Search..." />

      <label for="platformSelect">Platform</label>
      <select id="platformSelect">
        <option>All Platforms</option>
        <option>PlayStation</option>
        <option>Xbox</option>
        <option>PC</option>
        <option>Nintendo</option>
      </select>

      <label for="ageSelect">Age Rating</label>
      <select id="ageSelect">
        <option>All Ratings</option>
        <option>8</option>
        <option>13</option>
        <option>16</option>
        <option>18+</option>
      </select>

      <button type="button" onclick="applyFilters()">Apply Filters</button>
      <button type="button" onclick="resetFilters()" style="margin-top:10px;">Reset</button>
    </aside>

    <!-- PRODUCT GRID -->
      <section class="main-content-wrapper">
        <?php foreach ($games as $g): ?>
          <?php
            $platformForFilter = $platformMap[$g['platform']] ?? $g['platform'];

            // --- Image (exists -> use it, else placeholder) ---
            $baseUrl = "/Team_Project_TP2_Games_Store/Assets/Game_Images/";
            $placeholder = $baseUrl . "PlacerHolder.jpeg";

            $filename = trim((string)($g['image'] ?? ''));

            // Products_Page.php is in /Pages, so ../Assets points to /Assets
            $fsPath = __DIR__ . "/../Assets/Game_Images/" . $filename;

            $imgPath = (is_file($fsPath) && $filename !== "")
              ? $baseUrl . rawurlencode($filename)
              : $placeholder;

            // --- Price ---
            $priceLabel = "£" . number_format((float)$g['price'], 2);

            // --- data attributes for filtering ---
            $dataName = htmlspecialchars($g['name'], ENT_QUOTES);
            $dataPlatform = htmlspecialchars($platformForFilter, ENT_QUOTES);
            $dataAge = htmlspecialchars($g['age_restriction'], ENT_QUOTES);

            // --- JS-safe values ---
            $jsTitle = js($g['name']);
            $jsDesc  = js($g['description']);
            $jsPlat  = js($platformForFilter);
            $jsRate  = js($g['age_restriction']);
            $jsImg   = js($imgPath);
            $jsPrice = js($priceLabel);
          ?>

          <div class="product"
              data-name="<?= $dataName; ?>"
              data-platform="<?= $dataPlatform; ?>"
              data-age="<?= $dataAge; ?>">

            <p><?= htmlspecialchars(editionTag($g['age_restriction']), ENT_QUOTES); ?></p>

            <img
              src="<?= htmlspecialchars($imgPath, ENT_QUOTES); ?>"
              alt="<?= htmlspecialchars($g['name'], ENT_QUOTES); ?>"
              onerror="this.onerror=null; this.src='<?= htmlspecialchars($placeholder, ENT_QUOTES); ?>';"
            />

            <h3><?= htmlspecialchars(strtoupper($g['name']), ENT_QUOTES); ?></h3>

            <p><?= htmlspecialchars($priceLabel, ENT_QUOTES); ?></p>

            <button type="button"
              onclick="openProduct(
                <?= $jsTitle; ?>,
                <?= $jsImg; ?>,
                <?= $jsDesc; ?>,
                <?= $jsPrice; ?>,
                <?= $jsPlat; ?>,
                <?= $jsRate; ?>,
                <?= (int)$g['gid']; ?>
              )">
              View Details
            </button>

          </div>
        <?php endforeach; ?>
      </section>

  </div>

  <!-- PRODUCT MODAL -->
  <div id="productModal" class="product-modal" style="display:none;">
    <div id="modalContent" class="product-modal-content">

      <button id="product-close-btn" type="button" onclick="closeProduct()">Close</button>

      <h1 id="modalTitle"></h1>
      <img id="modalImage" src="" alt="Game cover" width="250" />
      <p id="modalDescription"></p>
      <h2 id="modalPrice"></h2>

      <p><strong>Platform:</strong> <span id="modalPlatform"></span></p>
      <p><strong>Age Rating:</strong> <span id="modalRating"></span></p>

      <?php if ($logged_in): ?>
        <!-- ADD TO CART -->
        <form method="post" action="add_to_cart.php">
          <input type="hidden" name="game_id" id="modalGameIdAdd" />
          <input type="hidden" name="redirect" value="stay" />
          <button type="submit">Add to Cart</button>
        </form>

        <!-- BUY NOW -->
        <form method="post" action="add_to_cart.php">
          <input type="hidden" name="game_id" id="modalGameIdBuy" />
          <input type="hidden" name="redirect" value="basket" />
          <button type="submit">Buy Now</button>
        </form>
      <?php else: ?>
        <p>Please <a href="Login_Page.php">log in</a> to add items to your basket.</p>
      <?php endif; ?>

      <hr />

      <h3>Reviews</h3>

      <?php if ($logged_in): ?>
        <form action="submit_review.php" method="post">
          <input type="hidden" name="game_id" id="modalGameIdReview" />

          <label>Rating (0–5):</label><br />
          <input type="number" name="rating" min="0" max="5" required /><br /><br />

          <label>Comment:</label><br />
          <textarea name="comment" rows="4" cols="40" required></textarea><br /><br />

          <input type="submit" value="Submit Review" />
          <input type="hidden" name="submitted" value="true" />
        </form>
      <?php else: ?>
        <p>Please <a href="Login_Page.php">log in</a> to leave a review.</p>
      <?php endif; ?>

    </div>
  </div>



</body>
</html>
