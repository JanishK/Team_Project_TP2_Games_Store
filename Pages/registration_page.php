<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('themes.php');

$error_message   = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitted'])) {
    require_once('connectdb.php');

    $username         = trim($_POST['username'] ?? '');
    $password         = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['Confirm_password'] ?? '');
    $email            = trim($_POST['email'] ?? '');

    if (!$username) {
        $error_message = "Please enter a valid username.";
    } elseif (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (!$password) {
        $error_message = "Please enter a password.";
    } elseif (!$password_confirm) {
        $error_message = "Please confirm your password.";
    } elseif ($password !== $password_confirm) {
        $error_message = "Passwords do not match.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stat = $db->prepare("INSERT INTO users (username, password, email, is_admin) VALUES (?, ?, ?, 0)");
            $stat->execute([$username, $password_hash, $email]);
            $success_message = "Welcome, $username! Your account has been created. You can now sign in.";
        } catch (PDOException $ex) {
            $error_message = "That username or email is already taken. Please try another.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

    <?php require_once __DIR__ . '/components/navbar.php'; ?>

    <div class="register-container">
        <h1>Create Account</h1>
        <p>Fill in the form below to get started.</p>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <div class="registration-form">
            <form method="post" action="registration_page.php">
                <input type="email"    name="email"            placeholder="Email address" required>
                <input type="text"     name="username"         placeholder="Username" required>
                <input type="password" name="password"         placeholder="Password" required>
                <input type="password" name="Confirm_password" placeholder="Confirm password" required>
                <input type="submit"   value="Create Account">
                <input type="hidden"   name="submitted" value="true">
            </form>
        </div>

        <p>Already have an account? <a href="Login_Page.php">Sign in</a></p>
    </div>

</body>
</html>