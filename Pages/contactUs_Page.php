<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/style.css">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <title>Contact Us</title>
</head>

<body class="<?php echo $themeClass; ?>">
    
            <!-- NAVIGATION BAR -->
    <nav class="cb-nav">
        <div class="cb-nav__container">
            
            <!-- Brand -->
            <a class="cb-brand" href="./home_Page.html">
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
    <main>
        <section class="contact-container">
            <h1 class="page-title">Contact Us</h1>
            <p class="upper-section">
                We're always here to help! Whether you have questions about our products, need support with an order, or
                require any other sort of feedback, feel free to get in touch by filling in the form below.
            </p>
            <p class="downer-section">
                You can also contact us on our email or through our phone number to get to one of our agents. <br>
                Email: CoreByte@gmail.com <br>
                Phone Number: 0121 742 9781
            </p>
            </section>

        <section class="contact-us-section">
            <form action="#" method="post">
                <label for="name">Full Name:</label><br>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required><br><br>

                <label for="email">Email Address:</label><br>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required><br><br>

                <label for="subject">Subject:</label><br>
                <input type="text" id="subject" name="subject" placeholder="What is your message about?"
                    required><br><br>

                <label for="message">Message:</label><br>
                <textarea id="message" name="message" rows="6" placeholder="Type your message here..."
                    required></textarea><br><br>

                <button type="submit">Submit</button>
            </form>
        </section>
    </main>
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
</body>

</html>