<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require_once("connectdb.php");
require_once("themes.php");


$isLoggedIn = isset($_SESSION['uid']);
$user_id = $isLoggedIn ? (int)$_SESSION['uid'] : 0;

// =======================================
// HANDLE CART ACTIONS (only if logged in)
// =======================================
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action  = $_POST['action'];
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;

    if ($action === "clear") {
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);

    } else {
        $check = $db->prepare("SELECT quantity FROM cart WHERE cart_id = ? AND user_id = ?");
        $check->execute([$cart_id, $user_id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $qty = (int)$row['quantity'];

            if ($action === "increment") {
                $qty++;
                $update = $db->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
                $update->execute([$qty, $cart_id, $user_id]);

            } elseif ($action === "decrement") {
                $qty--;
                if ($qty <= 0) {
                    $del = $db->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
                    $del->execute([$cart_id, $user_id]);
                } else {
                    $update = $db->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
                    $update->execute([$qty, $cart_id, $user_id]);
                }

            } elseif ($action === "remove") {
                $del = $db->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
                $del->execute([$cart_id, $user_id]);
            }
        }
    }

    header("Location: basket_Page.php");
    exit();
}

// =======================================
// FETCH CART ITEMS (only if logged in)
// =======================================
$items = [];
$total_items = 0;
$total_cost  = 0.00;

if ($isLoggedIn) {
    $sql = "
        SELECT c.cart_id, c.quantity,
               g.gid, g.name, g.platform, g.price, g.image
        FROM cart c
        JOIN games g ON c.game_id = g.gid
        WHERE c.user_id = ?
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/basket.css">


    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
    <title>Contact Us</title>

    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>
</head>


<body class="<?php echo $themeClass; ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<section class="basket-page-section">
    <h1>Your Basket</h1>


    <?php if (!$isLoggedIn): ?>
        <p>You need to be signed in to view your basket.</p>
        <a class="btn" href="./Login_Page.php">Sign in</a>

    <?php elseif (empty($items)): ?>
        <p>Your basket is empty.</p>

    <?php else: ?>

        <form method="POST">
            <button type="submit" name="action" value="clear" class="clear-btn">Remove all</button>
        </form>

        <div id="shopping_list">
            <?php foreach ($items as $item): ?>
                <div class="game_template">
                    <div class="basket_left">
                        <img src="../Assets/Game_Images/<?= htmlspecialchars($item['image']) ?>"
                             alt="<?= htmlspecialchars($item['name']) ?>">
                    </div>

                    <div class="basket_middle">
                        <label class="game_name"><?= htmlspecialchars($item['name']) ?></label>
                        <label class="game_price">£<?= number_format((float)$item['price'], 2) ?></label>
                        <label>Platform: <?= htmlspecialchars($item['platform']) ?></label>
                    </div>

                    <div class="basket_right">
                        <form method="POST">
                            <input type="hidden" name="cart_id" value="<?= (int)$item['cart_id'] ?>">
                            <button type="submit" name="action" value="remove" class="remove-btn">Remove</button>
                        </form>

                        <form method="POST" class="qty-controls">
                            <input type="hidden" name="cart_id" value="<?= (int)$item['cart_id'] ?>">
                            <button type="submit" name="action" value="decrement">-</button>
                            <label class="qty-num"><?= (int)$item['quantity'] ?></label>
                            <button type="submit" name="action" value="increment">+</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <section class="order-summary">
            <h3>Order Summary</h3>
            <p>Total items: <?= (int)$total_items ?></p>
            <p>Total cost: £<?= number_format((float)$total_cost, 2) ?></p>
            <button  class="checkout-btn">Proceed to checkout</button>
            <a href="checkout_Page.php" class="checkout-btn">Proceed to Checkout</a>
        </section>

    <?php endif; ?>
</section>
</body>