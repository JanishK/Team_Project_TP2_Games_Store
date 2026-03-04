<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$isLoggedIn = isset($_SESSION['username']);
$isAdmin    = (int)($_SESSION['is_admin'] ?? 0) === 1;

// Optional user fields (set these at login if you have them)
$displayName = $_SESSION['display_name'] ?? ($_SESSION['username'] ?? 'Guest');
$email       = $_SESSION['email'] ?? '';
$avatar      = $_SESSION['avatar_url'] ?? 'https://flowbite.com/docs/images/people/profile-picture-5.jpg';

// Project base path (fixes asset paths when in subfolder)
$BASE = '/Team_Project_TP2_Games_Store';
?>

<nav class="cb-nav">
  <div class="cb-nav__container">

    <!-- Brand -->
    <a class="cb-brand" href="<?= $BASE ?>/Pages/home_Page.php">
      <img class="cb-brand__logo" src="<?= $BASE ?>/Assets/Logo.png" alt="CoreByte Logo" />
      <span class="cb-brand__text">CoreByte</span>
    </a>

    <!-- Main links -->
    <ul class="cb-links" id="cbNavLinks">
      <li><a href="<?= $BASE ?>/Pages/home_Page.php" class="cb-link">Home</a></li>
      <li><a href="<?= $BASE ?>/Pages/Products_Page.php" class="cb-link">Products</a></li>
      <li><a href="<?= $BASE ?>/Pages/aboutUs_Page.php" class="cb-link">About</a></li>
    </ul>

    <!-- User avatar dropdown -->
    <div class="cb-user">
      <button class="cb-user__btn" type="button" id="cbUserBtn" aria-expanded="false" aria-controls="cbUserMenu">
        <span class="sr-only">Open user menu</span>
        <img class="cb-user__avatar" src="<?= htmlspecialchars($avatar) ?>" alt="User photo" />
      </button>

      <div class="cb-user__menu hidden" id="cbUserMenu" role="menu">

        <?php if ($isLoggedIn): ?>
          <div class="cb-user__header">
            <span class="cb-user__name"><?= htmlspecialchars($displayName) ?></span>
            <?php if (!empty($email)): ?>
              <span class="cb-user__email"><?= htmlspecialchars($email) ?></span>
            <?php endif; ?>
          </div>

          <a href="<?= $BASE ?>/Pages/basket_Page.php" role="menuitem">Basket</a>
          <a href="<?= $BASE ?>/Pages/registration_page.php" role="menuitem">Account</a>
          <a href="<?= $BASE ?>/Pages/settingsPage.php" role="menuitem">Settings</a>
          <a href="<?= $BASE ?>/Pages/contactUs_Page.php" role="menuitem">Support</a>

          <?php if ($isAdmin): ?>
            <a href="<?= $BASE ?>/Pages/Admin_Panel.php" role="menuitem" class="admin-item">Admin Panel</a>
          <?php endif; ?>

          <a href="<?= $BASE ?>/Pages/logout.php" role="menuitem">Sign out</a>

        <?php else: ?>
          <div class="cb-user__header">
            <span class="cb-user__name">Guest</span>
            <span class="cb-user__email">Sign in to access your account</span>
          </div>

          <a href="<?= $BASE ?>/Pages/Login_Page.php" role="menuitem">Sign in</a>
          <a href="<?= $BASE ?>/Pages/registration_page.php" role="menuitem">Create account</a>
          <a href="<?= $BASE ?>/Pages/contactUs_Page.php" role="menuitem">Support</a>
        <?php endif; ?>

      </div>
    </div>

  </div>
</nav>