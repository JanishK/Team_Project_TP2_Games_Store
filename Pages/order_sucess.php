<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['order_success'])) {
    header("Location: home_Page.php");
    exit();
}

require_once('themes.php');

$message = $_SESSION['order_success'];
$orderId = $_SESSION['last_order_id'] ?? null;

unset($_SESSION['order_success'], $_SESSION['last_order_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<div style="display:flex;align-items:center;justify-content:center;min-height:70vh;padding:20px;">
    <div class="settings-card" style="max-width:520px;width:100%;text-align:center;padding:40px 32px;">
        <div style="font-size:56px;margin-bottom:16px;">✅</div>

        <h1 style="color:var(--color-primary);font-size:clamp(22px,3vw,30px);margin-bottom:12px;">
            Order Placed!
        </h1>

        <p style="color:var(--text-muted);line-height:1.65;margin-bottom:8px;">
            <?= htmlspecialchars($message) ?>
        </p>

        <?php if ($orderId): ?>
            <p style="color:var(--text-muted);margin-bottom:24px;">
                Your order number is <strong style="color:var(--color-primary);">#<?= (int)$orderId ?></strong>
            </p>
        <?php endif; ?>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="Products_Page.php" class="cta-button">Continue Shopping</a>
            <a href="settingsPage.php?tab=orders" class="cta-button secondary-cta">View Orders</a>
            <a href="settingsPage.php?tab=transactions" class="cta-button secondary-cta">View Transactions</a>
        </div>
    </div>
</div>

</body>
</html>