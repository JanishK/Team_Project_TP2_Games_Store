<?php
session_start();
require_once('connectdb.php');

if (!isset($_SESSION['uid']) && !isset($_SESSION['username'])) {
    header("Location: Login_Page.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Resolve logged-in user safely
|--------------------------------------------------------------------------
*/
function resolveUserId(PDO $db): ?int {
    if (!empty($_SESSION['uid'])) {
        $stmt = $db->prepare("SELECT uid FROM users WHERE uid = ? LIMIT 1");
        $stmt->execute([(int)$_SESSION['uid']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return (int)$user['uid'];
        }
    }

    if (!empty($_SESSION['username'])) {
        $stmt = $db->prepare("SELECT uid FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([trim($_SESSION['username'])]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['uid'] = (int)$user['uid'];
            return (int)$user['uid'];
        }
    }

    return null;
}

$user_id = resolveUserId($db);

if (!$user_id) {
    session_unset();
    session_destroy();
    header("Location: Login_Page.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Generate CSRF token
|--------------------------------------------------------------------------
*/
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| Get user details (optional prefill)
|--------------------------------------------------------------------------
*/
$userDetails = [
    'full_name' => '',
    'email' => ''
];

try {
    $stmt = $db->prepare("SELECT username, email FROM users WHERE uid = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userDetails['full_name'] = $user['username'] ?? '';
        $userDetails['email'] = $user['email'] ?? '';
    }
} catch (PDOException $e) {
    // Safe fallback if columns differ
}

/*
|--------------------------------------------------------------------------
| Get cart items
|--------------------------------------------------------------------------
*/
$stmt = $db->prepare("
    SELECT 
        c.cart_id,
        c.quantity,
        g.gid,
        g.name,
        g.price,
        g.image,
        g.platform
    FROM cart c
    INNER JOIN games g ON c.game_id = g.gid
    WHERE c.user_id = ?
    ORDER BY c.cart_id DESC
");
$stmt->execute([$user_id]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Redirect if cart empty
|--------------------------------------------------------------------------
*/
if (!$cartItems) {
    header("Location: basket_Page.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Calculate totals
|--------------------------------------------------------------------------
*/
$subtotal = 0.00;

foreach ($cartItems as $item) {
    $lineTotal = (float)$item['price'] * (int)$item['quantity'];
    $subtotal += $lineTotal;
}

$delivery = 0.00; // digital store
$total = $subtotal + $delivery;

/*
|--------------------------------------------------------------------------
| Optional flash messages
|--------------------------------------------------------------------------
*/
$errorMessage = $_SESSION['checkout_error'] ?? '';
unset($_SESSION['checkout_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | CoreByte</title>
    <link rel="stylesheet" href="../CSS/style.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0f1117;
            color: #ffffff;
        }

        .checkout-wrapper {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #b8c2dc;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .back-link:hover {
            color: #ffffff;
        }

        .checkout-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .checkout-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        .checkout-card {
            background: #181c25;
            border: 1px solid #2b3140;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 90px 1fr auto;
            gap: 16px;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #2c3242;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image img {
            width: 90px;
            height: 110px;
            object-fit: cover;
            border-radius: 10px;
            background: #222;
            display: block;
        }

        .item-name {
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .item-meta {
            color: #b7bfd1;
            font-size: 0.92rem;
            margin-bottom: 4px;
        }

        .item-price {
            text-align: right;
            font-size: 1rem;
            font-weight: bold;
            white-space: nowrap;
        }

        .checkout-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #e9ecf5;
        }

        .checkout-form input,
        .checkout-form select {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 18px;
            border-radius: 10px;
            border: 1px solid #2d3445;
            background: #10141d;
            color: #ffffff;
            font-size: 0.95rem;
            box-sizing: border-box;
        }

        .checkout-form input:focus,
        .checkout-form select:focus {
            outline: none;
            border-color: #7c5cff;
        }

        .summary-box {
            margin-top: 10px;
            padding-top: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 14px 0;
            color: #d8def0;
        }

        .summary-row.total {
            font-size: 1.12rem;
            font-weight: bold;
            color: #ffffff;
            border-top: 1px solid #2c3242;
            padding-top: 18px;
            margin-top: 18px;
        }

        .checkout-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: #7c5cff;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s ease;
            margin-top: 10px;
        }

        .checkout-btn:hover {
            background: #6949f0;
        }

        .info-note {
            margin-top: 14px;
            font-size: 0.88rem;
            color: #aeb8d1;
            line-height: 1.5;
        }

        .error-message {
            background: rgba(255, 86, 86, 0.12);
            border: 1px solid rgba(255, 86, 86, 0.35);
            color: #ffb3b3;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        @media (max-width: 900px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .cart-item {
                grid-template-columns: 70px 1fr;
            }

            .item-price {
                grid-column: 2 / 3;
                text-align: left;
                margin-top: 6px;
            }

            .cart-item-image img {
                width: 70px;
                height: 88px;
            }
        }
    </style>
</head>
<body>

<div class="checkout-wrapper">
    <a href="basket_Page.php" class="back-link">← Back to Basket</a>
    <h1 class="checkout-title">Checkout</h1>

    <?php if (!empty($errorMessage)): ?>
        <div class="error-message">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <div class="checkout-layout">
        <!-- Left column -->
        <div class="checkout-card">
            <h2 class="section-title">Your Items</h2>

            <?php foreach ($cartItems as $item): ?>
                <?php
                    $quantity = (int)$item['quantity'];
                    $price = (float)$item['price'];
                    $lineTotal = $price * $quantity;
                ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img
                            src="../Assets/<?= htmlspecialchars($item['image']) ?>"
                            alt="<?= htmlspecialchars($item['name']) ?>"
                        >
                    </div>

                    <div>
                        <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="item-meta">Platform: <?= htmlspecialchars($item['platform']) ?></div>
                        <div class="item-meta">Quantity: <?= $quantity ?></div>
                        <div class="item-meta">Unit Price: £<?= number_format($price, 2) ?></div>
                    </div>

                    <div class="item-price">
                        £<?= number_format($lineTotal, 2) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Right column -->
        <div class="checkout-card">
            <h2 class="section-title">Payment Details</h2>

            <form class="checkout-form" method="post" action="place_order.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <label for="full_name">Full Name</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    required
                    maxlength="100"
                    value="<?= htmlspecialchars($userDetails['full_name']) ?>"
                >

                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    maxlength="150"
                    value="<?= htmlspecialchars($userDetails['email']) ?>"
                >

                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="">Select payment method</option>
                    <option value="Card">Card</option>
                    <option value="PayPal">PayPal</option>
                    <option value="Apple Pay">Apple Pay</option>
                </select>

                <div class="summary-box">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>£<?= number_format($subtotal, 2) ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Delivery</span>
                        <span>£<?= number_format($delivery, 2) ?></span>
                    </div>

                    <div class="summary-row total">
                        <span>Total</span>
                        <span>£<?= number_format($total, 2) ?></span>
                    </div>
                </div>

                <button type="submit" class="checkout-btn">Place Order</button>
                <p class="info-note">
                    Your basket will remain saved until the order is successfully placed.
                </p>
            </form>
        </div>
    </div>
</div>

</body>
</html>