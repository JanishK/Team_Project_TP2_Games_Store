<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$theme   = $_SESSION['theme'] ?? ($_COOKIE['theme'] ?? 'dark');
$allowed = ['dark', 'light'];

if (!in_array($theme, $allowed, true)) {
    $theme = 'dark';
}

$themeClass = ($theme === 'light') ? 'light-mode' : '';