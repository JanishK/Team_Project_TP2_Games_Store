<?php
session_start();

if (empty($_SESSION['order_success'])) {
    header("Location: basket_Page.php");
    exit();
}

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
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0f1117;
            color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .success-card {
            width: 90%;
            max-width: 520px;
            background: #181c25;
            border: 1px solid #2b3140;
            border-radius: 18px;
            padding: 32px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .success-card h1 {
            margin-top: 0;
            margin-bottom: 12px;
        }

        .success-card p {
            color: #cfd6e6;
            line-height: 1.6;
        }

        .success-card a {
            display: inline-block;
            margin-top: 18px;
            padding: 12px 18px;
            border-radius: 12px;
            background: #7c5cff;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .success-card a:hover {
            background: #6949f0;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <h1>Order Successful</h1>
        <p><?= htmlspecialchars($message) ?></p>

        <?php if ($orderId): ?>
            <p>Your order number is <strong>#<?= (int)$orderId ?></strong></p>
        <?php endif; ?>

        <a href="index.php">Continue Shopping</a>
    </div>
</body>
</html>