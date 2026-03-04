<?php
$error_message = '';
$success_message = '';
// Include database connection
include 'connectdb.php';
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $full_name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($full_name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "All fields are required.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
        exit;
    }

    // Insert data into the database
    try {
        $stmt = $db->prepare("INSERT INTO contact_us (full_name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $subject, $message]);
        $success_message = "Your message has been sent successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/contact_us.css">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">

    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <title>Contact Us</title>

    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>

<body class="<?php echo $themeClass; ?>">
    
            <!-- NAVIGATION BAR -->
        <?php require_once __DIR__ . '/components/navbar.php'; ?>

    <main>
    <?php
    if (!empty($error_message)) {
        echo '<div class="error-message">' . $error_message . '</div>';
    } elseif (!empty($success_message)) {
        echo '<div class="success-message">' . $success_message . '</div>';
    }
    ?>
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
            <form action="contactUs_Page.php" method="post">
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