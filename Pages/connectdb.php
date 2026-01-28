<?php
try {
  $db = new PDO("mysql:host=localhost;dbname=game_store;charset=utf8mb4", "root", "");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("DB connection failed: " . $e->getMessage());
}
