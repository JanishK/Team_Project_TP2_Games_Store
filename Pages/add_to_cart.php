<?php
session_start();
require_once('connectdb.php');

/*
|--------------------------------------------------------------------------
| Helper: redirect and stop
|--------------------------------------------------------------------------
*/
function redirectTo(string $location): void {
    header("Location: " . $location);
    exit();
}

/*
|--------------------------------------------------------------------------
| Must be logged in
|--------------------------------------------------------------------------
*/
if (empty($_SESSION['uid']) && empty($_SESSION['username'])) {
    redirectTo("Login_Page.php");
}

/*
|--------------------------------------------------------------------------
| Resolve logged-in user ID safely
|--------------------------------------------------------------------------
*/
$user_id = null;

if (!empty($_SESSION['uid'])) {
    $stmt = $db->prepare("SELECT uid FROM users WHERE uid = ?");
    $stmt->execute([(int)$_SESSION['uid']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_id = (int)$user['uid'];
    }
}

if (!$user_id && !empty($_SESSION['username'])) {
    $stmt = $db->prepare("SELECT uid FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_id = (int)$user['uid'];
        $_SESSION['uid'] = $user_id; // cache it for later
    }
}

if (!$user_id) {
    session_unset();
    session_destroy();
    redirectTo("Login_Page.php");
}

/*
|--------------------------------------------------------------------------
| Read and validate POST data
|--------------------------------------------------------------------------
*/
$game_id  = filter_input(INPUT_POST, 'game_id', FILTER_VALIDATE_INT);
$redirect = $_POST['redirect'] ?? 'stay';

if (!$game_id || $game_id <= 0) {
    redirectTo("Products_Page.php");
}

/*
|--------------------------------------------------------------------------
| Make sure the game exists
|--------------------------------------------------------------------------
*/
$stmt = $db->prepare("SELECT gid FROM games WHERE gid = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    redirectTo("Products_Page.php");
}

try {
    /*
    |--------------------------------------------------------------------------
    | BUY NOW: clear cart, add one item, go to basket
    |--------------------------------------------------------------------------
    */
    if ($redirect === 'basket') {
        $db->beginTransaction();

        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);

        $stmt = $db->prepare("
            INSERT INTO cart (user_id, game_id, quantity)
            VALUES (?, ?, 1)
        ");
        $stmt->execute([$user_id, $game_id]);

        $db->commit();
        redirectTo("basket_Page.php");
    }

    /*
    |--------------------------------------------------------------------------
    | NORMAL ADD TO CART
    |--------------------------------------------------------------------------
    | If item already exists, increment quantity.
    | Otherwise, insert a new row.
    |--------------------------------------------------------------------------
    */
    $stmt = $db->prepare("
        SELECT cart_id, quantity
        FROM cart
        WHERE user_id = ? AND game_id = ?
        LIMIT 1
    ");
    $stmt->execute([$user_id, $game_id]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        $stmt = $db->prepare("
            UPDATE cart
            SET quantity = quantity + 1
            WHERE cart_id = ?
        ");
        $stmt->execute([$cartItem['cart_id']]);
    } else {
        $stmt = $db->prepare("
            INSERT INTO cart (user_id, game_id, quantity)
            VALUES (?, ?, 1)
        ");
        $stmt->execute([$user_id, $game_id]);
    }

    redirectTo("Products_Page.php");

} catch (PDOException $ex) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    echo "<h3>Database error while adding to cart</h3>";
    echo "<p>" . htmlspecialchars($ex->getMessage()) . "</p>";
    exit();
}