<?php
$themeClass = "";
if (isset($_SESSION["username"])) {
  $stmt = $db->prepare("SELECT theme FROM user_settings WHERE username = ?");
  $stmt->execute([$_SESSION["username"]]);
  $theme = $stmt->fetchColumn();
  if ($theme === "light") $themeClass = "light-mode";
}
?>
