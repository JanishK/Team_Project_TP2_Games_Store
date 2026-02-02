<?php
session_start();
require_once('connectdb.php');

// Must be logged in
if (!isset($_SESSION["username"])) {
  header("Location: Login_Page.php");
  exit();
}

$username = $_SESSION["username"];
$success = "";
$error = "";

// Ensure a settings row exists for this user (so SELECT always returns something)
try {
  $stmt = $db->prepare("
    INSERT INTO user_settings (username)
    VALUES (?)
    ON DUPLICATE KEY UPDATE username = username
  ");
  $stmt->execute([$username]);
} catch (PDOException $ex) {
  $error = "Could not initialise settings.";
}

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Basic sanitisation
  $display_name = trim($_POST["display_name"] ?? "");
  $theme = ($_POST["theme"] ?? "dark") === "light" ? "light" : "dark";

  $email_notifications = isset($_POST["email_notifications"]) ? 1 : 0;
  $marketing_emails = isset($_POST["marketing_emails"]) ? 1 : 0;

  $currency = $_POST["currency"] ?? "GBP";
  $allowedCurrencies = ["GBP", "USD", "EUR"];
  if (!in_array($currency, $allowedCurrencies, true)) $currency = "GBP";

  $language = $_POST["language"] ?? "en";
  $allowedLanguages = ["en", "ne", "hi"];
  if (!in_array($language, $allowedLanguages, true)) $language = "en";

  try {
    $update = $db->prepare("
      UPDATE user_settings
      SET display_name = ?, theme = ?, email_notifications = ?, marketing_emails = ?, currency = ?, language = ?
      WHERE username = ?
    ");
    $update->execute([
      $display_name !== "" ? $display_name : null,
      $theme,
      $email_notifications,
      $marketing_emails,
      $currency,
      $language,
      $username
    ]);

    $success = "Settings updated successfully!";
  } catch (PDOException $ex) {
    $error = "Failed to save settings. Please try again.";
  }
}

// Load settings to show in the form
$settings = [
  "display_name" => null,
  "theme" => "dark",
  "email_notifications" => 1,
  "marketing_emails" => 0,
  "currency" => "GBP",
  "language" => "en"
];

try {
  $get = $db->prepare("SELECT * FROM user_settings WHERE username = ?");
  $get->execute([$username]);
  $row = $get->fetch(PDO::FETCH_ASSOC);
  if ($row) $settings = array_merge($settings, $row);
} catch (PDOException $ex) {
  $error = $error ?: "Failed to load settings.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Settings</title>

  <!-- Your existing CSS -->
  <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css?v=dev">

  <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png" />
</head>

<body class="<?php echo $themeClass; ?>">

     <!-- NAVIGATION BAR (your cb-nav) -->
  <nav class="cb-nav">
    <div class="cb-nav__container">

      <a class="cb-brand" href="./home_Page.php">
        <img class="cb-brand__logo" src="/Team_Project_TP2_Games_Store/Assets/Logo.png" alt="CoreByte Logo" />
        <span class="cb-brand__text">CoreByte</span>
      </a>

      <ul class="cb-links" id="cbNavLinks">
        <li><a href="./home_Page.php" class="cb-link">Home</a></li>
        <li><a href="./Products_Page.php" class="cb-link is-active">Products</a></li>
        <li><a href="./aboutUs_Page.php" class="cb-link">About</a></li>
      </ul>

      <div class="cb-user">
        <button class="cb-user__btn" type="button" id="cbUserBtn" aria-expanded="false" aria-controls="cbUserMenu">
          <span class="sr-only">Open user menu</span>
          <img class="cb-user__avatar" src="https://flowbite.com/docs/images/people/profile-picture-5.jpg" alt="User photo" />
        </button>

        <div class="cb-user__menu hidden" id="cbUserMenu" role="menu">
          <div class="cb-user__header">
            <span class="cb-user__name">Janish Kandel</span>
            <span class="cb-user__email">JanishK@corebyte.com</span>
          </div>

          <a href="./basket_Page.php" role="menuitem">Basket</a>
          <a href="./registration_page.php" role="menuitem">Account</a>
          <a href="./settingsPage.php" role="menuitem">Settings</a>
          <a href="./contactUs_Page.php" role="menuitem">Support</a>
          <a href="#" role="menuitem">Sign out</a>
        </div>
      </div>

    </div>
  </nav>

  <!-- Page content -->
  <main class="settings-container">
    <h1>Settings</h1>

    <?php if ($error): ?>
      <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form class="settings-form" method="POST" action="./settingsPage.php">
      <!-- Display Name -->
      <label for="display_name">Display name</label>
      <input
        type="text"
        id="display_name"
        name="display_name"
        placeholder="e.g., Janish"
        value="<?php echo htmlspecialchars($settings['display_name'] ?? ''); ?>"
      />

      <!-- Theme -->
      <label for="theme">Theme</label>
      <select id="theme" name="theme">
        <option value="dark" <?php echo ($settings["theme"] === "dark") ? "selected" : ""; ?>>Dark</option>
        <option value="light" <?php echo ($settings["theme"] === "light") ? "selected" : ""; ?>>Light</option>
      </select>

      <!-- Currency -->
      <label for="currency">Currency</label>
      <select id="currency" name="currency">
        <option value="GBP" <?php echo ($settings["currency"] === "GBP") ? "selected" : ""; ?>>GBP (£)</option>
        <option value="USD" <?php echo ($settings["currency"] === "USD") ? "selected" : ""; ?>>USD ($)</option>
        <option value="EUR" <?php echo ($settings["currency"] === "EUR") ? "selected" : ""; ?>>EUR (€)</option>
      </select>

      <!-- Language -->
      <label for="language">Language</label>
      <select id="language" name="language">
        <option value="en" <?php echo ($settings["language"] === "en") ? "selected" : ""; ?>>English</option>
        <option value="ne" <?php echo ($settings["language"] === "ne") ? "selected" : ""; ?>>Nepali</option>
        <option value="hi" <?php echo ($settings["language"] === "hi") ? "selected" : ""; ?>>Hindi</option>
      </select>

      <!-- Toggles -->
      <div class="settings-toggles">
        <label >
          <input type="checkbox" name="email_notifications" <?php echo ($settings["email_notifications"] ? "checked" : ""); ?> />
          Email notifications (orders, delivery updates)
        </label>

        <label >
          <input type="checkbox" name="marketing_emails" <?php echo ($settings["marketing_emails"] ? "checked" : ""); ?> />
          Marketing emails (deals & promos)
        </label>
      </div>

      <button type="submit" >Save Settings</button>
    </form>
  </main>

  <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
</body>
</html>
