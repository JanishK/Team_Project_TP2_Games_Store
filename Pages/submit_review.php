<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');

/* Must be logged in */
if (!isset($_SESSION['uid']) && !isset($_SESSION['username'])) {
    header("Location: Login_Page.php");
    exit();
}

/* Resolve user id */
$user_id = null;
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

if (!$user_id) {
    header("Location: Login_Page.php");
    exit();
}

$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$rating  = isset($_POST['rating'])  ? (int)$_POST['rating']  : -1;
$comment = trim($_POST['comment'] ?? '');

/* Validate */
if ($game_id <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
    header("Location: productDetails.php?id=$game_id&review_error=1");
    exit();
}

try {
    $stmt = $db->prepare("
        INSERT INTO reviews (game_id, user_id, rating, comment)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$game_id, $user_id, $rating, $comment]);
} catch (PDOException $e) {
    header("Location: productDetails.php?id=$game_id&review_error=1");
    exit();
}

header("Location: productDetails.php?id=$game_id&review_success=1");
exit();