<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');
require_once('themes.php');

$isLoggedIn = isset($_SESSION['uid']) || isset($_SESSION['username']);
$user_id    = 0;

if ($isLoggedIn) {
    /* Resolve uid */
    if (!empty($_SESSION['uid'])) {
        $user_id = (int)$_SESSION['uid'];
    } elseif (!empty($_SESSION['username'])) {
        $st = $db->prepare("SELECT uid FROM users WHERE username = ? LIMIT 1");
        $st->execute([$_SESSION['username']]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $user_id = (int)$row['uid'];
            $_SESSION['uid'] = $user_id;
        }
    }
}

/* ---- Handle POST actions ---- */
if ($isLoggedIn && $user_id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action  = $_POST['action'];
    $cart_id = (int)($_POST['cart_id'] ?? 0);

    if ($action === 'clear') {
        $db->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

    } elseif ($cart_id > 0) {
        $check = $db->prepare("SELECT quantity FROM cart WHERE cart_id = ? AND user_id = ?");
        $check->execute([$cart_id, $user_id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $qty = (int)$row['quantity'];
            if ($action === 'increment') {
                $db->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?")
                   ->execute([$qty + 1, $cart_id, $user_id]);

            } elseif ($action === 'decrement') {
                if ($qty <= 1) {
                    $db->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?")
                       ->execute([$cart_id, $user_id]);
                } else {
                    $db->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?")
                       ->execute([$qty - 1, $cart_id, $user_id]);
                }
            } elseif ($action === 'remove') {
                $db->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?")
                   ->execute([$cart_id, $user_id]);
            }
        }
    }

    header("Location: basket_Page.php");
    exit();
}

/* ---- Fetch cart ---- */
$items       = [];
$total_items = 0;
$total_cost  = 0.00;

if ($isLoggedIn && $user_id > 0) {
    $st = $db->prepare("
        SELECT c.cart_id, c.quantity,
               g.gid, g.name, g.platform, g.price, g.image
        FROM cart c
        JOIN games g ON c.game_id = g.gid
        WHERE c.user_id = ?
        ORDER BY c.cart_id DESC
    ");
    $st->execute([$user_id]);
    $items = $st->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {
        $total_items += (int)$item['quantity'];
        $total_cost  += (int)$item['quantity'] * (float)$item['price'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basket | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/basket.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<section class="basket-page-section">
    <h1>Your Basket</h1>

    <?php if (!$isLoggedIn || $user_id === 0): ?>
        <p>You need to be signed in to view your basket.</p>
        <a class="cta-button" href="Login_Page.php">Sign In</a>

    <?php elseif (empty($items)): ?>
        <p>Your basket is empty.</p>
        <a class="cta-button secondary-cta" href="Products_Page.php">Browse Products</a>

    <?php else: ?>

        <form method="POST" style="margin-bottom:12px;">
            <button type="submit" name="action" value="clear" class="settings-btn danger">
                Remove all items
            </button>
        </form>

        <div id="shopping_list">
            <?php foreach ($items as $item): ?>
            <div class="game_template">

                <div class="basket_left">
                    <img src="/Team_Project_TP2_Games_Store/Assets/Game_Images/<?= htmlspecialchars($item['image']) ?>"
                         alt="<?= htmlspecialchars($item['name']) ?>">
                </div>

                <div class="basket_middle">
                    <span class="game_name"><?= htmlspecialchars($item['name']) ?></span>
                    <span class="game_price">£<?= number_format((float)$item['price'], 2) ?></span>
                    <span>Platform: <?= htmlspecialchars($item['platform']) ?></span>
                </div>

                <div class="basket_right">
                    <form method="POST">
                        <input type="hidden" name="cart_id" value="<?= (int)$item['cart_id'] ?>">
                        <button type="submit" name="action" value="remove" class="settings-btn danger">
                            Remove
                        </button>
                    </form>

                    <form method="POST" class="qty-controls">
                        <input type="hidden" name="cart_id" value="<?= (int)$item['cart_id'] ?>">
                        <button type="submit" name="action" value="decrement">−</button>
                        <span class="qty-num"><?= (int)$item['quantity'] ?></span>
                        <button type="submit" name="action" value="increment">+</button>
                    </form>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <section class="order-summary">
            <h3>Order Summary</h3>
            <p>Total items: <strong><?= $total_items ?></strong></p>
            <p>Total cost: <strong>£<?= number_format($total_cost, 2) ?></strong></p>
            <a href="checkout_Page.php" class="cta-button">Proceed to Checkout →</a>
        </section>

    <?php endif; ?>
</section>

</body>
</html>