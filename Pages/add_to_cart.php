<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');

function redirectTo(string $location): never {
    header("Location: " . $location);
    exit();
}

/* Must be logged in */
if (empty($_SESSION['uid']) && empty($_SESSION['username'])) {
    redirectTo("Login_Page.php");
}

/* Resolve user id */
$user_id = null;

if (!empty($_SESSION['uid'])) {
    $st = $db->prepare("SELECT uid FROM users WHERE uid = ? LIMIT 1");
    $st->execute([(int)$_SESSION['uid']]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if ($row) $user_id = (int)$row['uid'];
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
    redirectTo("Login_Page.php");
}

/* Validate game_id */
$game_id  = filter_input(INPUT_POST, 'game_id', FILTER_VALIDATE_INT);
$redirect = $_POST['redirect'] ?? 'stay';

if (!$game_id || $game_id <= 0) {
    redirectTo("Products_Page.php");
}

/* Check game exists */
$st = $db->prepare("SELECT gid FROM games WHERE gid = ? LIMIT 1");
$st->execute([$game_id]);
if (!$st->fetch()) {
    redirectTo("Products_Page.php");
}

try {
    if ($redirect === 'basket') {
        /* Buy Now: clear cart, add just this item */
        $db->beginTransaction();
        $db->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
        $db->prepare("INSERT INTO cart (user_id, game_id, quantity) VALUES (?, ?, 1)")
           ->execute([$user_id, $game_id]);
        $db->commit();
        redirectTo("basket_Page.php");
    }

    /* Normal add: increment or insert */
    $st = $db->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND game_id = ? LIMIT 1");
    $st->execute([$user_id, $game_id]);
    $existing = $st->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $db->prepare("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ?")
           ->execute([$existing['cart_id']]);
    } else {
        $db->prepare("INSERT INTO cart (user_id, game_id, quantity) VALUES (?, ?, 1)")
           ->execute([$user_id, $game_id]);
    }

    /* Redirect back to wherever the add came from */
    $back = $_SERVER['HTTP_REFERER'] ?? 'Products_Page.php';
    redirectTo($back);

} catch (PDOException $ex) {
    if ($db->inTransaction()) $db->rollBack();
    redirectTo("Products_Page.php");
}