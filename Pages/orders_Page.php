<?php
session_start();
require_once('connectdb.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: basket_Page.php");
    exit();
}

if (!isset($_SESSION['username'])) {
    header("Location: Login_Page.php");
    exit();
}

$username = $_SESSION['username'];

/*
|--------------------------------------------------------------------------
| CSRF check
|--------------------------------------------------------------------------
*/
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['checkout_error'] = "Invalid request. Please refresh and try again.";
    header("Location: checkout_Page.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Validate user
|--------------------------------------------------------------------------
*/
$stmt = $db->prepare("SELECT uid, username, email FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: Login_Page.php");
    exit();
}

$user_id = (int)$user['uid'];

/*
|--------------------------------------------------------------------------
| Validate form input
|--------------------------------------------------------------------------
*/
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');

$allowedMethods = ['Card', 'PayPal', 'Apple Pay'];

if ($full_name === '' || $email === '' || $payment_method === '') {
    $_SESSION['checkout_error'] = "Please complete all required fields.";
    header("Location: checkout_Page.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['checkout_error'] = "Please enter a valid email address.";
    header("Location: checkout_Page.php");
    exit();
}

if (!in_array($payment_method, $allowedMethods, true)) {
    $_SESSION['checkout_error'] = "Invalid payment method selected.";
    header("Location: checkout_Page.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Get cart items
|--------------------------------------------------------------------------
*/
$stmt = $db->prepare("
    SELECT 
        c.cart_id,
        c.game_id,
        c.quantity,
        g.gid,
        g.name,
        g.price
    FROM cart c
    INNER JOIN games g ON c.game_id = g.gid
    WHERE c.user_id = ?
    ORDER BY c.cart_id DESC
");
$stmt->execute([$user_id]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$cartItems) {
    $_SESSION['checkout_error'] = "Your basket is empty.";
    header("Location: basket_Page.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Calculate total
|--------------------------------------------------------------------------
*/
$subtotal = 0.00;

foreach ($cartItems as $item) {
    $subtotal += ((float)$item['price'] * (int)$item['quantity']);
}

$total = $subtotal;

/*
|--------------------------------------------------------------------------
| Save order
|--------------------------------------------------------------------------
*/
try {
    $db->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | IMPORTANT:
    | This INSERT assumes your orders table has these columns:
    | order_id, username, full_name, email, payment_method, total_amount, status, created_at
    |--------------------------------------------------------------------------
    */
    $stmtOrder = $db->prepare("
        INSERT INTO orders (
            username,
            full_name,
            email,
            payment_method,
            total_amount,
            status,
            created_at
        )
        VALUES (?, ?, ?, ?, ?, 'Paid', NOW())
    ");
    $stmtOrder->execute([
        $username,
        $full_name,
        $email,
        $payment_method,
        $total
    ]);

    $order_id = (int)$db->lastInsertId();

    /*
    |--------------------------------------------------------------------------
    | This INSERT assumes your order_items table has:
    | order_item_id, order_id, game_id, game_name, unit_price, quantity, line_total
    |--------------------------------------------------------------------------
    */
    $stmtItem = $db->prepare("
        INSERT INTO order_items (
            order_id,
            game_id,
            game_name,
            unit_price,
            quantity,
            line_total
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($cartItems as $item) {
        $game_id = (int)$item['game_id'];
        $game_name = $item['name'];
        $unit_price = (float)$item['price'];
        $quantity = (int)$item['quantity'];
        $line_total = $unit_price * $quantity;

        $stmtItem->execute([
            $order_id,
            $game_id,
            $game_name,
            $unit_price,
            $quantity,
            $line_total
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Clear cart only after successful order creation
    |--------------------------------------------------------------------------
    */
    $stmtDelete = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmtDelete->execute([$user_id]);

    $db->commit();

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    $_SESSION['checkout_error'] = "Order failed: " . $e->getMessage();
    header("Location: checkout_Page.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Send email after commit
|--------------------------------------------------------------------------
*/
$subject = "CoreByte Order Confirmation #{$order_id}";

$message = "Hello {$full_name},\n\n";
$message .= "Your payment has been received successfully.\n\n";
$message .= "Order Number: #{$order_id}\n";
$message .= "Payment Method: {$payment_method}\n";
$message .= "Total Paid: £" . number_format($total, 2) . "\n\n";
$message .= "Items:\n";

foreach ($cartItems as $item) {
    $lineTotal = (float)$item['price'] * (int)$item['quantity'];
    $message .= "- {$item['name']} x" . (int)$item['quantity'] . " - £" . number_format($lineTotal, 2) . "\n";
}

$message .= "\nThank you for shopping with CoreByte.";

$headers = "From: no-reply@corebyte.co.uk\r\n";
$headers .= "Reply-To: no-reply@corebyte.co.uk\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

@mail($email, $subject, $message, $headers);

$_SESSION['order_success'] = "Your order has been placed successfully.";
$_SESSION['last_order_id'] = $order_id;

header("Location: order_success.php");
exit();