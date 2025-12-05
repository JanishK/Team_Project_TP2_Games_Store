<?php
session_start();
require_once('connectdb.php');

// Must be logged in
if (!isset($_SESSION['username']) && !isset($_SESSION['uid'])) {
    header("Location: Login_Page.php");
    exit();
}

// -----------------------------
// Resolve user_id (uid)
// -----------------------------
$user_id = null;

if (isset($_SESSION['uid'])) {
    $user_id = (int)$_SESSION['uid'];
} elseif (isset($_SESSION['username'])) {
    $u = $db->prepare("SELECT uid FROM users WHERE username = ?");
    $u->execute([$_SESSION['username']]);
    $row = $u->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $user_id = (int)$row['uid'];
        $_SESSION['uid'] = $user_id;   // cache for next time
    }
}

if (!$user_id) {
    header("Location: Login_Page.php");
    exit();
}

// -----------------------------
// Read POST data
// -----------------------------
$game_id  = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;  // IMPORTANT: game_id
$redirect = $_POST['redirect'] ?? 'stay'; // 'stay' or 'basket'

if ($game_id <= 0) {
    header("Location: Products_Page.php");
    exit();
}

// -----------------------------
// Check game exists (prevents FK error)
// -----------------------------
$gstmt = $db->prepare("SELECT gid FROM games WHERE gid = ?");
$gstmt->execute([$game_id]);

if ($gstmt->rowCount() === 0) {
    // game id not in table -> avoid FK constraint
    header("Location: Products_Page.php");
    exit();
}

try {

    if ($redirect === 'basket') {
        // -------------------------
        // BUY NOW:
        // Clear cart, insert ONE row, go to basket
        // -------------------------
        $clear = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear->execute([$user_id]);

        $insert = $db->prepare("INSERT INTO cart (user_id, game_id, quantity)
                                VALUES (?, ?, 1)");
        $insert->execute([$user_id, $game_id]);

        header("Location: basket_Page.php");
        exit();

    } else {
        // -------------------------
        // NORMAL ADD TO CART:
        // increment if exists, else insert
        // -------------------------
        $cstmt = $db->prepare("SELECT quantity FROM cart WHERE user_id = ? AND game_id = ?");
        $cstmt->execute([$user_id, $game_id]);

        if ($cstmt->rowCount() > 0) {
            $up = $db->prepare("UPDATE cart
                                SET quantity = quantity + 1
                                WHERE user_id = ? AND game_id = ?");
            $up->execute([$user_id, $game_id]);
        } else {
            $ins = $db->prepare("INSERT INTO cart (user_id, game_id, quantity)
                                 VALUES (?, ?, 1)");
            $ins->execute([$user_id, $game_id]);
        }

        header("Location: Products_Page.php");
        exit();
    }

} catch (PDOException $ex) {
    echo "Database error while adding to cart:<br>";
    echo htmlspecialchars($ex->getMessage());
    exit();
}
