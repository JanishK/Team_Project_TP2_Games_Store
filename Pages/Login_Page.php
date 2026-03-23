<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('themes.php');

$error_message = '';

/* Already logged in — redirect */
if (isset($_SESSION['username'])) {
    header("Location: home_Page.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitted'])) {

    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error_message = "Please fill in both username and password.";
    } else {
        require_once('connectdb.php');
        try {
            $stat = $db->prepare("SELECT uid, password, is_admin, email FROM users WHERE username = ? LIMIT 1");
            $stat->execute([trim($_POST['username'])]);

            if ($stat->rowCount() > 0) {
                $row = $stat->fetch(PDO::FETCH_ASSOC);

                if (password_verify($_POST['password'], $row['password'])) {
                    session_regenerate_id(true); // prevent session fixation

                    $_SESSION['uid']      = (int)$row['uid'];
                    $_SESSION['username'] = trim($_POST['username']);
                    $_SESSION['is_admin'] = (int)$row['is_admin'];
                    $_SESSION['email']    = $row['email'] ?? '';

                    if ((int)$row['is_admin'] === 1) {
                        header("Location: Admin_Panel.php");
                    } else {
                        header("Location: home_Page.php");
                    }
                    exit();
                } else {
                    $error_message = "Incorrect password. Please try again.";
                }
            } else {
                $error_message = "Username not found.";
            }
        } catch (PDOException $ex) {
            $error_message = "Database error. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

    <?php require_once __DIR__ . '/components/navbar.php'; ?>

    <div class="login-container">
        <h1>Sign In</h1>
        <p>Welcome back — enter your details to continue.</p>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="login-form">
            <form action="Login_Page.php" method="post">
                <input type="text"     name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit"   value="Sign In">
                <input type="hidden"   name="submitted" value="true">
            </form>
        </div>

        <p>Don't have an account? <a href="registration_page.php">Register</a></p>
    </div>

</body>
</html>