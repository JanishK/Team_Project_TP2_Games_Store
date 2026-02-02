<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png" />


</head>

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

            <div class="trendingcontainer">

                <div class="product2"
                    data-name="Mario Kart 8 Deluxe"
                    data-platform="Nintendo"
                    data-genre="Racing">
                    <p>STANDARD EDITION</p>
                    <img src="../Assets/Game_Images/Mario_Cart_Deluxe_8.jpg" alt="Mario Kart 8 Deluxe">
                    <h3>MARIO KART 8 DELUXE</h3>
                    <p>£39.99</p>
                </div>

                <div class="product2"
                    data-name="Mario Kart 8 Deluxe"
                    data-platform="Nintendo"
                    data-genre="Racing">
                    <p>STANDARD EDITION</p>
                    <img src="../Assets/Game_Images/Mario_Cart_Deluxe_8.jpg" alt="Mario Kart 8 Deluxe">
                    <h3>MARIO KART 8 DELUXE</h3>
                    <p>£39.99</p>
                </div>

                <div class="product2"
                    data-name="Mario Kart 8 Deluxe"
                    data-platform="Nintendo"
                    data-genre="Racing">
                    <p>STANDARD EDITION</p>
                    <img src="../Assets/Game_Images/Mario_Cart_Deluxe_8.jpg" alt="Mario Kart 8 Deluxe">
                    <h3>MARIO KART 8 DELUXE</h3>
                    <p>£39.99</p>
                </div>

                <div class="product2"
                    data-name="Mario Kart 8 Deluxe"
                    data-platform="Nintendo"
                    data-genre="Racing">
                    <p>STANDARD EDITION</p>
                    <img src="../Assets/Game_Images/Mario_Cart_Deluxe_8.jpg" alt="Mario Kart 8 Deluxe">
                    <h3>MARIO KART 8 DELUXE</h3>
                    <p>£39.99</p>
                </div>

            </div>
        </section>

        <!-- DEALS -->
        <section class="deals-section">
            <h2 class="section-title">Deals of the Week</h2>

            <div class="deals-container">

                <div class="deal-card">
                    <img src="YOUR_IMAGE_HERE" alt="">
                    <h3>Game Title Here</h3>
                    <p class="old-price">£59.99</p>
                    <p class="new-price">£29.99</p>
                </div>

                <div class="deal-card">
                    <img src="YOUR_IMAGE_HERE" alt="">
                    <h3>Game Title Here</h3>
                    <p class="old-price">£49.99</p>
                    <p class="new-price">£19.99</p>
                </div>

                <div class="deal-card">
                    <img src="YOUR_IMAGE_HERE" alt="">
                    <h3>Game Title Here</h3>
                    <p class="old-price">£39.99</p>
                    <p class="new-price">£14.99</p>
                </div>

            </div>
        </section>

        <!-- PLATFORM LOGOS -->
        <section class="platform-logos">
            <h2 class="section-title">Available On</h2>

            <div class="logo-row">
                <img src="/Assets/ICONS/PlayStation_logo.png" alt="PlayStation">
                <img src="/Assets/ICONS/Xbox_one_logo.png" alt="Xbox">
                <img src="/Assets/ICONS/Nintendo_logo.png" alt="Nintendo">
                <img src="/Assets/ICONS/PC_LOGO_2.png" alt="PC">
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
