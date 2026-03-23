<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');
require_once('themes.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<main>
    <section class="about-page-content">

        <header class="about-header">
            <h1 class="page-title">About CoreByte</h1>
            <p class="about-subtitle">
                Instant digital game downloads, trusted sellers, and a smoother way to shop across platforms.
            </p>
        </header>

        <div class="about-main-grid">

            <!-- Left column -->
            <div class="grid-text-column">

                <article class="mission-section">
                    <h2 class="section-heading">What we do</h2>
                    <p>
                        CoreByte is a digital marketplace built for gamers who want fast access to great
                        titles — without the hassle. Browse by platform, discover new releases and deals,
                        and get your games delivered instantly.
                    </p>
                </article>

                <article class="commitment-section">
                    <h2 class="section-heading">Why gamers choose CoreByte</h2>
                    <ul class="commitment-list">
                        <li><span class="icon-check">✓</span> Curated catalogue across major platforms.</li>
                        <li><span class="icon-check">✓</span> Secure checkout and protected customer data.</li>
                        <li><span class="icon-check">✓</span> Instant delivery for digital purchases.</li>
                        <li><span class="icon-check">✓</span> Helpful support when you need it.</li>
                    </ul>
                </article>

                <section class="about-stats">
                    <h2 class="section-heading">Built for speed &amp; trust</h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Instant Delivery</h3>
                            <p>Digital downloads delivered fast after purchase.</p>
                        </div>
                        <div class="stat-card">
                            <h3>Secure Payments</h3>
                            <p>Trusted payment flow with security-first design.</p>
                        </div>
                        <div class="stat-card">
                            <h3>Curated Deals</h3>
                            <p>Featured offers to help you save on top titles.</p>
                        </div>
                    </div>
                </section>

                <div class="cta-section">
                    <p>Questions, refunds, or account help? We're here.</p>
                    <div class="cta-row">
                        <a href="contactUs_Page.php"  class="cta-button">Contact Support</a>
                        <a href="Products_Page.php"   class="cta-button secondary-cta">Shop Games</a>
                    </div>
                </div>

            </div>

            <!-- Right column -->
            <div class="grid-image-column">
                <img src="/Team_Project_TP2_Games_Store/CoreByte Website Images/Gamer Picture.jpg"
                     alt="Gamer wearing headphones playing on a PC."
                     class="responsive-image"
                     onerror="this.style.display='none'">
                <div class="image-caption">
                    <p><strong>CoreByte</strong> — where your next game is one click away.</p>
                </div>
            </div>

        </div>
    </section>
</main>

<footer>
    <div class="footer-box">
        <div class="footer-header">
            <h3>CoreByte</h3>
            <p>Your digital storefront for games, deals, and instant downloads.</p>
        </div>
        <div class="footer-columns">
            <div class="footer-section">
                <h3>Shop</h3>
                <ul>
                    <li><a href="Products_Page.php">All Products</a></li>
                    <li><a href="Products_Page.php">Deals</a></li>
                    <li><a href="Products_Page.php">Trending</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Support</h3>
                <ul>
                    <li><a href="contactUs_Page.php">Help Centre</a></li>
                    <li><a href="contactUs_Page.php">Refund Policy</a></li>
                    <li><a href="contactUs_Page.php">Terms &amp; Privacy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Follow</h3>
                <p>Instagram / TikTok / YouTube</p>
                <p style="font-size:13px;opacity:.7;">@CoreByteStore</p>
            </div>
        </div>
        <p class="copyright">© 2025 CoreByte. All rights reserved.</p>
    </div>
</footer>

</body>
</html>