<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: basket_Page.php");
    exit();
}

if (!isset($_SESSION['uid']) && !isset($_SESSION['username'])) {
    header("Location: Login_Page.php");
    exit();
}

/* --------------------------------------------------
   Resolve user ID
-------------------------------------------------- */
$user_id = null;
$username = null;

if (!empty($_SESSION['uid'])) {
    $stmt = $db->prepare("SELECT uid, username, email FROM users WHERE uid = ? LIMIT 1");
    $stmt->execute([(int)$_SESSION['uid']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $user_id = (int)$row['uid'];
        $username = $row['username'];
    }
}

if (!$user_id && !empty($_SESSION['username'])) {
    $stmt = $db->prepare("SELECT uid, username, email FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$_SESSION['username']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $user_id = (int)$row['uid'];
        $username = $row['username'];
        $_SESSION['uid'] = $user_id;
    }
}

if (!$user_id) {
    session_unset();
    session_destroy();
    header("Location: Login_Page.php");
    exit();
}

/* --------------------------------------------------
   CSRF check
-------------------------------------------------- */
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['checkout_error'] = "Invalid request. Please refresh and try again.";
    header("Location: checkout_Page.php");
    exit();
}

/* --------------------------------------------------
   Validate form input
-------------------------------------------------- */
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');

$_SESSION['checkout_old'] = [
    'full_name' => $full_name,
    'email' => $email,
    'payment_method' => $payment_method
];

$allowed_methods = ['Card', 'PayPal', 'Apple Pay'];

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

if (!in_array($payment_method, $allowed_methods, true)) {
    $_SESSION['checkout_error'] = "Please select a valid payment method.";
    header("Location: checkout_Page.php");
    exit();
}

/* --------------------------------------------------
   Get cart items
-------------------------------------------------- */
$stmt = $db->prepare("
    SELECT c.cart_id, c.game_id, c.quantity, g.name, g.price
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

/* --------------------------------------------------
   Calculate total
-------------------------------------------------- */
$total = 0.00;
foreach ($cartItems as $item) {
    $total += (float)$item['price'] * (int)$item['quantity'];
}

/* --------------------------------------------------
   Save order in transaction
-------------------------------------------------- */
try {
    $db->beginTransaction();

    $stmtOrder = $db->prepare("
        INSERT INTO orders (
            user_id,
            username,
            full_name,
            email,
            payment_method,
            total_amount,
            status,
            created_at
        )
        VALUES (?, ?, ?, ?, ?, ?, 'Completed', NOW())
    ");
    $stmtOrder->execute([
        $user_id,
        $username,
        $full_name,
        $email,
        $payment_method,
        $total
    ]);

    $order_id = (int)$db->lastInsertId();

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
        $unit_price = (float)$item['price'];
        $quantity   = (int)$item['quantity'];
        $line_total = $unit_price * $quantity;

        $stmtItem->execute([
            $order_id,
            (int)$item['game_id'],
            $item['name'],
            $unit_price,
            $quantity,
            $line_total
        ]);
    }

    $stmtTxn = $db->prepare("
        INSERT INTO transactions (
            order_id,
            user_id,
            payment_method,
            amount,
            transaction_status,
            created_at
        )
        VALUES (?, ?, ?, ?, 'Successful', NOW())
    ");
    $stmtTxn->execute([
        $order_id,
        $user_id,
        $payment_method,
        $total
    ]);

    $stmtDelete = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmtDelete->execute([$user_id]);

    $db->commit();

} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    $_SESSION['checkout_error'] = "Order failed: " . $e->getMessage();
    header("Location: checkout_Page.php");
    exit();
}

/* --------------------------------------------------
   Optional email
-------------------------------------------------- */
$subject = "CoreByte Order Confirmation #{$order_id}";

$message = "Hello {$full_name},\n\n";
$message .= "Your order has been placed successfully.\n\n";
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

/* --------------------------------------------------
   Flash success
-------------------------------------------------- */
unset($_SESSION['checkout_old']);
$_SESSION['order_success'] = "Your order has been placed successfully.";
$_SESSION['last_order_id'] = $order_id;

header("Location: /Team_Project_TP2_Games_Store/Pages/order_sucess.php");
exit();