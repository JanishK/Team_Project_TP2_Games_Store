<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');
require_once('themes.php');

$error_message   = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['name']    ?? '');
    $email     = trim($_POST['email']   ?? '');
    $subject   = trim($_POST['subject'] ?? '');
    $message   = trim($_POST['message'] ?? '');

    if (!$full_name || !$email || !$subject || !$message) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO contact_us (full_name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $subject, $message]);
            $success_message = "Your message has been sent! We'll get back to you soon.";
        } catch (PDOException $e) {
            $error_message = "Sorry, there was a problem sending your message. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/contact_us.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<main>

    <?php if (!empty($error_message)): ?>
        <div class="error-message" style="max-width:640px;margin:16px auto;">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="success-message" style="max-width:640px;margin:16px auto;">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>

    <section class="contact-container">
        <h1 class="page-title">Contact Us</h1>
        <p class="upper-section">
            We're always here to help! Whether you have questions about our products, need support
            with an order, or have feedback — fill in the form below.
        </p>
        <p class="downer-section">
            You can also reach us directly:<br>
            <strong>Email:</strong> CoreByte@gmail.com<br>
            <strong>Phone:</strong> 0121 742 9781
        </p>
    </section>

    <section class="contact-us-section">
        <form action="contactUs_Page.php" method="post">

            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Your full name" required
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="your@email.com" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" placeholder="What is your message about?" required
                   value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">

            <label for="message">Message</label>
            <textarea id="message" name="message" rows="6"
                      placeholder="Type your message here…" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>

            <button type="submit">Send Message</button>
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
            </div>
        </div>
        <p class="copyright">© 2025 CoreByte. All rights reserved.</p>
    </div>
</footer>

</body>
</html>