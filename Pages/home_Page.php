<?php
session_start();
require_once('connectdb.php');
/* Optional: simple “edition” label from age rating (just a display tag) */
function editionTag($age) {
  return ($age === '18+') ? 'ADULT EDITION' : 'STANDARD EDITION';
}
try{
    $stmt = $db->query("SELECT gid, name, description, age_restriction, platform, price,discount, image FROM games ORDER BY view DESC LIMIT 8");
    $trendingGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    die("Database error: " . $ex->getMessage());
}
try {
    $stmt = $db->query("SELECT gid, name, description, age_restriction, platform, price, image, discount
FROM games
WHERE discount > 0
ORDER BY discount DESC
LIMIT 8
");
    $deals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    die("Database error: " . $ex->getMessage());
}
function js($value) {
  return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png" />

    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>

<body class="<?php echo $themeClass; ?>">
        <!-- NAVIGATION BAR -->
    <?php require_once __DIR__ . '/components/navbar.php'; ?>
   
    <script>
        const userBtn = document.getElementById("cbUserBtn");
        const userMenu = document.getElementById("cbUserMenu");

        userBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            userMenu.classList.toggle("hidden");
            userBtn.setAttribute("aria-expanded", String(!userMenu.classList.contains("hidden")));
        });

        document.addEventListener("click", () => {
            userMenu.classList.add("hidden");
            userBtn.setAttribute("aria-expanded", "false");
        });
    </script>



    <div>
        <section class="hero-banner">
            <div class="hero-content">
                <h1>Welcome to CoreByte</h1>
                <p>Your one-stop store for digital games, deals & instant downloads.</p>
                <button class="hero-btn">Shop Now</button>
            </div>
        </section>

        <!-- GAME CATEGORIES -->
        <section class="categories-section">
            <h2 class="section-title">Browse by Category</h2>
            <div class="categories-container">
                <button class="category-card">Action</button>
                <button class="category-card">Adventure</button>
                <button class="category-card">RPG</button>
                <button class="category-card">Racing</button>
                <button class="category-card">Sports</button>
                <button class="category-card">Horror</button>
            </div>    
        </section>

        <!-- TRENDING GAMES -->
        <section class="trending-section">
            <h2 class="section-title">Games Trending</h2>
            <!-- PRODUCT GRID -->
      <section class="main-content-wrapper">
        <?php foreach ($trendingGames as $game): ?>
          <?php
            // --- Image (exists -> use it, else placeholder) ---
            $baseUrl = "/Team_Project_TP2_Games_Store/Assets/Game_Images/";
            $placeholder = $baseUrl . "PlacerHolder.jpeg";

            $filename = trim((string)($game['image'] ?? ''));

            // Products_Page.php is in /Pages, so ../Assets points to /Assets
            $fsPath = __DIR__ . "/../Assets/Game_Images/" . $filename;

            $imgPath = (is_file($fsPath) && $filename !== "")
              ? $baseUrl . rawurlencode($filename)
              : $placeholder;

            // --- Price ---
            $discount = (int)$game['discount'];
            if ($discount > 0) {
                $discountedPrice = $game['price'] * (1 - $discount / 100);
                $priceLabel = "£" . number_format($discountedPrice, 2) . " (was £" . number_format((float)$game['price'], 2) . ")";
            } else {
                $priceLabel = "£" . number_format((float)$game['price'], 2);
            }
            // --- JS-safe values ---
            $jsTitle = js($game['name']);
            $jsDesc  = js($game['description']);
            $jsRate  = js($game['age_restriction']);
            $jsImg   = js($imgPath);
            $jsPrice = js($priceLabel);
          ?>

          <div class="product"
              data-name="<?= $dataName; ?>"
              data-platform="<?= $dataPlatform; ?>"
              data-age="<?= $dataAge; ?>">

            <p><?= htmlspecialchars(editionTag($game['age_restriction']), ENT_QUOTES); ?></p>

            <img
              src="<?= htmlspecialchars($imgPath, ENT_QUOTES); ?>"
              alt="<?= htmlspecialchars($game['name'], ENT_QUOTES); ?>"
              onerror="this.onerror=null; this.src='<?= htmlspecialchars($placeholder, ENT_QUOTES); ?>';"
            />

            <h3><?= htmlspecialchars(strtoupper($game['name']), ENT_QUOTES); ?></h3>

            <p><?= htmlspecialchars($priceLabel, ENT_QUOTES); ?></p>

        <form method="post" action="add_to_cart.php">
            <input type="hidden" name="game_id" value="<?= $game['gid']; ?>">
            <button type="submit">Add To Cart</button>
        </form>


          </div>
        <?php endforeach; ?>
      </section>

        <!-- DEALS -->
        <section class="trending-section">
            <h2 class="section-title">Deals of the Week</h2>
            <!-- PRODUCT GRID -->
        <section class="main-content-wrapper">
            <?php foreach ($deals as $game): ?>
          <?php
            // --- Image (exists -> use it, else placeholder) ---
            $baseUrl = "/Team_Project_TP2_Games_Store/Assets/Game_Images/";
            $placeholder = $baseUrl . "PlacerHolder.jpeg";

            $filename = trim((string)($game['image'] ?? ''));

            // Products_Page.php is in /Pages, so ../Assets points to /Assets
            $fsPath = __DIR__ . "/../Assets/Game_Images/" . $filename;

            $imgPath = (is_file($fsPath) && $filename !== "")
              ? $baseUrl . rawurlencode($filename)
              : $placeholder;

            // --- Price ---
            $discount = (int)$game['discount'];
            if ($discount > 0) {
                $discountedPrice = $game['price'] * (1 - $discount / 100);
                $priceLabel = "£" . number_format($discountedPrice, 2) . " (was £" . number_format((float)$game['price'], 2) . ")";
            } else {
                $priceLabel = "£" . number_format((float)$game['price'], 2);
            }
            
            // --- JS-safe values ---
            $jsTitle = js($game['name']);
            $jsDesc  = js($game['description']);
            $jsRate  = js($game['age_restriction']);
            $jsImg   = js($imgPath);
            $jsPrice = js($priceLabel);
            ?>
            <div class="product"
                data-name="<?= $dataName; ?>"
                data-platform="<?= $dataPlatform; ?>"
                data-age="<?= $dataAge; ?>">
                <p><?= htmlspecialchars(editionTag($game['age_restriction']), ENT_QUOTES); ?></p>
                <img
                src="<?= htmlspecialchars($imgPath, ENT_QUOTES); ?>"
                alt="<?= htmlspecialchars($game['name'], ENT_QUOTES); ?>"
                onerror="this.onerror=null; this.src='<?= htmlspecialchars($placeholder, ENT_QUOTES); ?>';"
                />
                <h3><?= htmlspecialchars(strtoupper($game['name']), ENT_QUOTES); ?></h3>
                <p><?= htmlspecialchars($priceLabel, ENT_QUOTES); ?></p>
                <form method="post" action="add_to_cart.php">
                    <input type="hidden" name="game_id" value="<?= $game['gid']; ?>">
                    <button type="submit">Add To Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
        </section>
            </div>
        </section>

        <!-- PLATFORM LOGOS -->
        <section class="platform-logos">
            <h2 class="section-title">Available On</h2>

            <div class="logo-row">
                <img src="../Assets/ICONS/PlayStation_logo.png" alt="PlayStation">
                <img src="../Assets/ICONS/Xbox_one_logo.png" alt="Xbox">
                <img src="../Assets/ICONS/Nintendo_logo.png" alt="Nintendo">
                <img src="../Assets/ICONS/PC_LOGO_2.png" alt="PC">
            </div>
        </section>

        <!-- NEWSLETTER -->
        <section class="newsletter-section">
            <h2>Join the CoreByte Community</h2>
            <p>Get exclusive offers, updates and early access to discounts.</p>

            <div class="newsletter-box">
                <input type="email" placeholder="Enter your email">
                <button>Subscribe</button>
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
                            <li><a href="#">Home</a></li>
                            <li><a href="#">Products</a></li>
                            <li><a href="#">About</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                    </div>

                    <div class="footer-section">
                        <h3>Support</h3>
                        <ul>
                            <li><a href="#">FAQ</a></li>
                            <li><a href="#">Customer Service</a></li>
                            <li><a href="#">Refund Policy</a></li>
                        </ul>
                    </div>

                    <div class="footer-section">
                        <h3>Follow Us</h3>
                        <p>Instagram / TikTok / YouTube</p>
                    </div>

                </div>

                <p class="copyright">© 2025 CoreByte. All rights reserved.</p>
            </div>
        </footer>

        <script src="app.js"></script>
        <script>
            toggleTheme();
        </script>



    </div>
</body>
</html>