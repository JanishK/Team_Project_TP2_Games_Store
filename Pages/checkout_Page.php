<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');
require_once('themes.php');

if (!isset($_SESSION['uid']) && !isset($_SESSION['username'])) {
    header("Location: Login_Page.php");
    exit();
}

/* ---- Resolve user ---- */
$user_id = null;
if (!empty($_SESSION['uid'])) {
    $st = $db->prepare("SELECT uid FROM users WHERE uid = ? LIMIT 1");
    $st->execute([(int)$_SESSION['uid']]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $user_id = (int)$row['uid'];
    }
}
if (!$user_id && !empty($_SESSION['username'])) {
    $st = $db->prepare("SELECT uid FROM users WHERE username = ? LIMIT 1");
    $st->execute([$_SESSION['username']]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $user_id = (int)$row['uid'];
        $_SESSION['uid'] = $user_id;
    }
}
if (!$user_id) {
    session_unset();
    session_destroy();
    header("Location: Login_Page.php");
    exit();
}

/* ---- CSRF token ---- */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ---- User details for prefill ---- */
$prefill = ['full_name' => '', 'email' => ''];
try {
    $st = $db->prepare("SELECT username, email FROM users WHERE uid = ? LIMIT 1");
    $st->execute([$user_id]);
    $u = $st->fetch(PDO::FETCH_ASSOC);
    if ($u) {
        $prefill['full_name'] = $u['username'] ?? '';
        $prefill['email']     = $u['email'] ?? '';
    }
} catch (PDOException $e) {
}

/* ---- Old form values after failed checkout ---- */
$formData = $_SESSION['checkout_old'] ?? [];
unset($_SESSION['checkout_old']);

/* ---- Flash error ---- */
$errorMessage = $_SESSION['checkout_error'] ?? '';
unset($_SESSION['checkout_error']);

/* ---- Cart items ---- */
$st = $db->prepare("
    SELECT c.cart_id, c.quantity, g.gid, g.name, g.price, g.image, g.platform
    FROM cart c
    INNER JOIN games g ON c.game_id = g.gid
    WHERE c.user_id = ?
    ORDER BY c.cart_id DESC
");
$st->execute([$user_id]);
$cartItems = $st->fetchAll(PDO::FETCH_ASSOC);

if (!$cartItems) {
    header("Location: basket_Page.php");
    exit();
}

/* ---- Totals ---- */
$subtotal = 0.00;
foreach ($cartItems as $item) {
    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}
$total = $subtotal;

$selectedPayment = $formData['payment_method'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/checkout.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<div class="checkout-wrapper">
    <a href="basket_Page.php" class="checkout-back-link">← Back to Basket</a>
    <h1 class="checkout-title">Checkout</h1>

    <?php if (!empty($errorMessage)): ?>
        <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <div class="checkout-layout">

        <div class="checkout-card">
            <h2 class="checkout-section-title">Your Items</h2>

            <?php foreach ($cartItems as $item):
                $qty       = (int)$item['quantity'];
                $price     = (float)$item['price'];
                $lineTotal = $price * $qty;
                $imgSrc    = "/Team_Project_TP2_Games_Store/Assets/Game_Images/" . htmlspecialchars($item['image']);
            ?>
            <div class="checkout-item">
                <div class="checkout-item-image">
                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                </div>
                <div class="checkout-item-info">
                    <div class="checkout-item-name"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="checkout-item-meta">Platform: <?= htmlspecialchars($item['platform']) ?></div>
                    <div class="checkout-item-meta">Quantity: <?= $qty ?></div>
                    <div class="checkout-item-meta">Unit price: £<?= number_format($price, 2) ?></div>
                </div>
                <div class="checkout-item-price">£<?= number_format($lineTotal, 2) ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="checkout-card">
            <h2 class="checkout-section-title">Payment Details</h2>

            <form class="checkout-form" method="post" action="place_order.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <label for="full_name">Full Name</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    required
                    maxlength="100"
                    value="<?= htmlspecialchars($formData['full_name'] ?? $prefill['full_name']) ?>"
                >

                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    maxlength="150"
                    value="<?= htmlspecialchars($formData['email'] ?? $prefill['email']) ?>"
                >

                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="">Select payment method</option>
                    <option value="Card" <?= $selectedPayment === 'Card' ? 'selected' : '' ?>>Card</option>
                    <option value="PayPal" <?= $selectedPayment === 'PayPal' ? 'selected' : '' ?>>PayPal</option>
                    <option value="Apple Pay" <?= $selectedPayment === 'Apple Pay' ? 'selected' : '' ?>>Apple Pay</option>
                </select>

                <div class="checkout-summary">
                    <div class="checkout-summary-row">
                        <span>Subtotal</span>
                        <span>£<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="checkout-summary-row">
                        <span>Delivery</span>
                        <span>£0.00</span>
                    </div>
                    <div class="checkout-summary-row checkout-summary-total">
                        <span>Total</span>
                        <span>£<?= number_format($total, 2) ?></span>
                    </div>
                </div>

                <button type="submit" class="cta-button">Place Order</button>

                <p class="checkout-note">
                    Your basket is saved until the order is successfully placed.
                </p>
            </form>
        </div>

    </div>
</div>

</body>
</html>