<?php
session_start();
require_once('connectdb.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("ERROR: Request was not POST.");
}

if (!isset($_SESSION['username'])) {
    die("ERROR: User is not logged in.");
}

$username = $_SESSION['username'];

/*
|--------------------------------------------------------------------------
| Get logged-in user ID
|--------------------------------------------------------------------------
*/
$stmt = $db->prepare("SELECT uid FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("ERROR: Could not find logged-in user in users table.");
}

$user_id = (int)$user['uid'];

/*
|--------------------------------------------------------------------------
| Validate payment method
|--------------------------------------------------------------------------
*/
$payment_method = trim($_POST['payment_method'] ?? '');

if ($payment_method === '') {
    die("ERROR: No payment method received from form.");
}

/*
|--------------------------------------------------------------------------
| Get cart items
|--------------------------------------------------------------------------
*/
$stmt = $db->prepare("
    SELECT 
        c.game_id,
        c.quantity,
        g.price
    FROM cart c
    INNER JOIN games g ON c.game_id = g.gid
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$cartItems) {
    die("ERROR: No cart items found for this user.");
}

/*
|--------------------------------------------------------------------------
| Calculate order total
|--------------------------------------------------------------------------
*/
$total = 0.00;

foreach ($cartItems as $item) {
    $total += ((float)$item['price'] * (int)$item['quantity']);
}

try {
    $db->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Insert into orders
    |--------------------------------------------------------------------------
    */
    $stmt = $db->prepare("
        INSERT INTO orders (
            user_id,
            total_amount,
            payment_method,
            order_status
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $total,
        $payment_method,
        'Completed'
    ]);

    $order_id = (int)$db->lastInsertId();

    if ($order_id <= 0) {
        throw new Exception("Order insert failed: no order_id returned.");
    }

    /*
    |--------------------------------------------------------------------------
    | Insert order items
    |--------------------------------------------------------------------------
    */
    $stmtItem = $db->prepare("
        INSERT INTO order_items (
            order_id,
            game_id,
            quantity,
            price
        )
        VALUES (?, ?, ?, ?)
    ");

    foreach ($cartItems as $item) {
        $stmtItem->execute([
            $order_id,
            (int)$item['game_id'],
            (int)$item['quantity'],
            (float)$item['price']
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Clear cart
    |--------------------------------------------------------------------------
    */
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $db->commit();

    $_SESSION['order_success'] = "Your order has been placed successfully.";
    $_SESSION['last_order_id'] = $order_id;

    header("Location: order_success.php");
    exit();

} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    die("ORDER ERROR: " . $e->getMessage());
}