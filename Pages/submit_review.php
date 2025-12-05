<?php
session_start();
require_once('connectdb.php');

echo "<pre>";
print_r($_POST);
echo "</pre>";

require_once('connectdb.php');

if (!isset($_SESSION['uid'])) {
    header("Location: Login_Page.php");
    exit();
}

$user_id = (int)$_SESSION['uid'];

$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$rating  = isset($_POST['rating']) ? (int)$_POST['rating'] : -1;
$comment = trim($_POST['comment'] ?? '');

if ($game_id <= 0 || $rating < 0 || $rating > 5 || $comment === '') {
    header("Location: Products_Page.php");
    exit();
}

$stmt = $db->prepare("INSERT INTO reviews (game_id, user_id, rating, comment)
                      VALUES (?, ?, ?, ?)");
$stmt->execute([$game_id, $user_id, $rating, $comment]);

header("Location: Products_Page.php");
exit();
?>
