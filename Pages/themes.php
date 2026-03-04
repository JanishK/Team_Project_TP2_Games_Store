<?php
declare(strict_types=1);

/**
 * themes.php
 * - NO database needed
 * - Theme is stored in session and/or cookie
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default
$theme = $_SESSION['theme'] ?? ($_COOKIE['theme'] ?? 'dark');

// Validate
$allowed = ['dark', 'light'];
if (!in_array($theme, $allowed, true)) {
    $theme = 'dark';
}

// Provide a body class to use in HTML
$themeClass = ($theme === 'light') ? 'theme-light' : 'theme-dark';