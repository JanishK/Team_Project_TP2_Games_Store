<?php
require_once('connectdb.php');

$stmt = $db->query("SELECT COUNT(*) AS total FROM games");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Connected ✅ Games in DB: " . $row['total'];
