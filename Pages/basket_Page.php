<?php
session_start();
require_once("connectdb.php");

// =======================================
// USER MUST BE LOGGED IN
// =======================================
if (!isset($_SESSION['uid'])) {
    header("Location: Login_Page.php");
    exit();
}

$user_id = (int)$_SESSION['uid'];

// =======================================
// HANDLE CART ACTIONS (increment, decrement, remove, clear)
// =======================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action  = $_POST['action'];
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;

    if ($action === "clear") {
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);

    } else {
        // Validate row belongs to logged-in user
        $check = $db->prepare("SELECT quantity FROM cart WHERE cart_id = ? AND user_id = ?");
        $check->execute([$cart_id, $user_id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $qty = (int)$row['quantity'];

            if ($action === "increment") {
                $qty++;
                $update = $db->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
                $update->execute([$qty, $cart_id]);

            } elseif ($action === "decrement") {
                $qty--;
                if ($qty <= 0) {
                    $del = $db->prepare("DELETE FROM cart WHERE cart_id = ?");
                    $del->execute([$cart_id]);
                } else {
                    $update = $db->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
                    $update->execute([$qty, $cart_id]);
                }

            } elseif ($action === "remove") {
                $del = $db->prepare("DELETE FROM cart WHERE cart_id = ?");
                $del->execute([$cart_id]);
            }
        }
    }

    header("Location: basket_Page.php");
    exit();
}

// =======================================
// FETCH CART ITEMS
// =======================================
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

// Totals
$total_items = 0;
$total_cost  = 0.00;

foreach ($items as $item) {
    $total_items += $item['quantity'];
    $total_cost  += $item['quantity'] * (float)$item['price'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreByte | Basket</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="icon" type="image/png" href="../Assets/Logo.png">
</head>

<body>

<!-- NAVBAR -->
<div class="nav-bar">
    <ul class="nav-left">
        <img class="page_logo" src="../Assets/Logo.png" alt="Logo">
        <li><a href="./home_Page.html">Home</a></li>
        <li><a href="./Products_Page.php">Products</a></li>
        <li><a href="./aboutUs_Page.html">About</a></li>
    </ul>

    <ul class="nav-right">
        <li><a href="./contactUs_Page.html"><img src="../Assets/Support.svg" class="basket-icon"></a></li>
        <li><a href="./Login_Page.php"><img src="../Assets/Account.svg" class="basket-icon"></a></li>
        <li><a href="./basket_Page.php"><img src="../Assets/Basket.svg" class="basket-icon"></a></li>
    </ul>
</div>

<section class="basket-page-section">

    <h1>Your Basket</h1>

    <?php if (empty($items)): ?>
        <p>Your basket is empty.</p>

    <?php else: ?>

        <!-- CLEAR CART -->
        <form method="POST">
            <button type="submit" name="action" value="clear" class="clear-btn">Remove all</button>
        </form>

        <div id="shopping_list">

            <?php foreach ($items as $item): ?>
                <div class="game_template">

                    <!-- IMAGE -->
                    <div class="basket_left">
                        <img src="../Assets/Game_Images/<?= htmlspecialchars($item['image']) ?>"
                             alt="<?= htmlspecialchars($item['name']) ?>">
                    </div>

                    <!-- NAME + PRICE -->
                    <div class="basket_middle">
                        <label class="game_name"><?= htmlspecialchars($item['name']) ?></label>
                        <label class="game_price">£<?= number_format($item['price'], 2) ?></label>
                        <label>Platform: <?= htmlspecialchars($item['platform']) ?></label>
                    </div>

                    <!-- QUANTITY + REMOVE -->
                    <div class="basket_right">

                        <!-- REMOVE -->
                        <form method="POST">
                            <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                            <button type="submit" name="action" value="remove" class="remove-btn">Remove</button>
                        </form>

                        <!-- QUANTITY CONTROLS -->
                        <form method="POST" class="qty-controls">
                            <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">

                            <button type="submit" name="action" value="decrement">-</button>

                            <label class="qty-num"><?= $item['quantity'] ?></label>

                            <button type="submit" name="action" value="increment">+</button>
                        </form>

                    </div>
                </div>
            <?php endforeach; ?>

        </div>

        <section class="order-summary">
            <h3>Order Summary</h3>
            <p>Total items: <?= $total_items ?></p>
            <p>Total cost: £<?= number_format($total_cost, 2) ?></p>

            <button class="checkout-btn">Proceed to checkout</button>
        </section>

    <?php endif; ?>

</section>

</body>
</html>
