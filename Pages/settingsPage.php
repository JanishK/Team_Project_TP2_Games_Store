<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');
require_once('themes.php');

if (!isset($_SESSION['username'])) {
    header("Location: Login_Page.php");
    exit();
}

/* --------------------------------------------------
   Resolve current user
-------------------------------------------------- */
$user_id  = 0;
$username = $_SESSION['username'];

if (!empty($_SESSION['uid'])) {
    $user_id = (int)$_SESSION['uid'];
} else {
    $st = $db->prepare("SELECT uid FROM users WHERE username = ? LIMIT 1");
    $st->execute([$username]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $user_id = (int)$row['uid'];
        $_SESSION['uid'] = $user_id;
    }
}

if ($user_id <= 0) {
    session_unset();
    session_destroy();
    header("Location: Login_Page.php");
    exit();
}

/* --------------------------------------------------
   Flash / messages
-------------------------------------------------- */
$error_message   = '';
$success_message = '';

if (isset($_GET['theme_updated'])) {
    $success_message = "Theme updated successfully.";
}

/* --------------------------------------------------
   Handle form submissions
-------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $new_email = trim($_POST['email'] ?? '');
        $new_pass  = trim($_POST['new_password'] ?? '');
        $conf_pass = trim($_POST['confirm_password'] ?? '');

        if ($new_email !== '' && !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address.";
        } elseif ($new_pass !== '' && $new_pass !== $conf_pass) {
            $error_message = "Passwords do not match.";
        } else {
            try {
                if ($new_email !== '') {
                    $db->prepare("UPDATE users SET email = ? WHERE uid = ?")
                       ->execute([$new_email, $user_id]);
                    $_SESSION['email'] = $new_email;
                }

                if ($new_pass !== '') {
                    $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                    $db->prepare("UPDATE users SET password = ? WHERE uid = ?")
                       ->execute([$hash, $user_id]);
                }

                $success_message = "Profile updated successfully.";
            } catch (PDOException $e) {
                $error_message = "Error updating profile. Please try again.";
            }
        }
    }

    elseif ($_POST['action'] === 'update_theme') {
        $theme = trim($_POST['theme'] ?? 'dark');
        $allowedThemes = ['dark', 'light'];

        if (!in_array($theme, $allowedThemes, true)) {
            $error_message = "Invalid theme selected.";
        } else {
            $_SESSION['theme'] = $theme;
            header("Location: settingsPage.php?tab=appearance&theme_updated=1");
            exit();
        }
    }
}

/* --------------------------------------------------
   Fetch current user details
-------------------------------------------------- */
$userDetails = [];

try {
    $st = $db->prepare("SELECT username, email FROM users WHERE uid = ? LIMIT 1");
    $st->execute([$user_id]);
    $userDetails = $st->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    $userDetails = [];
}

/* --------------------------------------------------
   Fetch orders
-------------------------------------------------- */
$orders = [];

try {
    $st = $db->prepare("
        SELECT
            o.order_id,
            o.total_amount,
            o.status,
            o.created_at,
            GROUP_CONCAT(oi.game_name SEPARATOR ', ') AS items
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.order_id
        WHERE o.user_id = ?
        GROUP BY o.order_id
        ORDER BY o.created_at DESC
        LIMIT 20
    ");
    $st->execute([$user_id]);
    $orders = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        $st = $db->prepare("
            SELECT
                o.order_id,
                o.total_amount,
                o.status,
                o.created_at,
                GROUP_CONCAT(oi.game_name SEPARATOR ', ') AS items
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.order_id
            WHERE o.username = ?
            GROUP BY o.order_id
            ORDER BY o.created_at DESC
            LIMIT 20
        ");
        $st->execute([$username]);
        $orders = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        $orders = [];
    }
}

/* --------------------------------------------------
   Fetch transactions
-------------------------------------------------- */
$transactions = [];

try {
    $st = $db->prepare("
        SELECT
            transaction_id,
            order_id,
            payment_method,
            amount,
            transaction_status,
            created_at
        FROM transactions
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $st->execute([$user_id]);
    $transactions = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $transactions = [];
}

/* --------------------------------------------------
   Active tab
-------------------------------------------------- */
$tab = $_GET['tab'] ?? 'profile';
$allowedTabs = ['profile', 'security', 'appearance', 'orders', 'transactions', 'notifications'];

if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'profile';
}

$currentTheme = $_SESSION['theme'] ?? 'dark';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/settings.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<div class="settings-container">
    <div class="settings-header">
        <div>
            <h1 class="settings-title">Settings</h1>
            <p class="settings-subtitle">Manage your account and preferences.</p>
        </div>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="settings-alert error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="settings-alert success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <div class="settings-grid">
        <aside class="settings-sidebar">
            <nav class="settings-nav">
                <a href="?tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>">👤 Profile</a>
                <a href="?tab=security" class="<?= $tab === 'security' ? 'active' : '' ?>">🔒 Security</a>
                <a href="?tab=appearance" class="<?= $tab === 'appearance' ? 'active' : '' ?>">🎨 Appearance</a>
                <a href="?tab=orders" class="<?= $tab === 'orders' ? 'active' : '' ?>">📦 Orders</a>
                <a href="?tab=transactions" class="<?= $tab === 'transactions' ? 'active' : '' ?>">💳 Transactions</a>
                <a href="?tab=notifications" class="<?= $tab === 'notifications' ? 'active' : '' ?>">🔔 Notifications</a>
            </nav>
        </aside>

        <div class="settings-main">

            <?php if ($tab === 'profile'): ?>
                <div class="settings-card">
                    <h2>Profile</h2>
                    <p class="helper">Update your account email address.</p>

                    <form class="settings-form" method="post">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="settings-row">
                            <label>Username</label>
                            <input
                                type="text"
                                value="<?= htmlspecialchars($userDetails['username'] ?? $username) ?>"
                                disabled
                                style="opacity:.6;cursor:not-allowed;"
                            >
                        </div>

                        <div class="settings-row">
                            <label for="email">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="New email address"
                                value="<?= htmlspecialchars($userDetails['email'] ?? '') ?>"
                            >
                        </div>

                        <div class="settings-actions">
                            <button type="submit" class="settings-btn primary">Save Changes</button>
                        </div>
                    </form>
                </div>

            <?php elseif ($tab === 'security'): ?>
                <div class="settings-card">
                    <h2>Security</h2>
                    <p class="helper">Change your password. Leave blank to keep your current one.</p>

                    <form class="settings-form" method="post">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="settings-row">
                            <label for="new_password">New Password</label>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                placeholder="Enter new password"
                                autocomplete="new-password"
                            >
                        </div>

                        <div class="settings-row">
                            <label for="confirm_password">Confirm</label>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Confirm new password"
                                autocomplete="new-password"
                            >
                        </div>

                        <div class="settings-actions">
                            <button type="submit" class="settings-btn primary">Update Password</button>
                        </div>
                    </form>
                </div>

                <div class="settings-card">
                    <h2>Danger Zone</h2>
                    <p class="helper">Irreversible actions — please be certain.</p>

                    <div class="settings-actions">
                        <a href="logout.php" class="settings-btn danger">Sign Out</a>
                    </div>
                </div>

            <?php elseif ($tab === 'appearance'): ?>
                <div class="settings-card">
                    <h2>Appearance</h2>
                    <p class="helper">Choose between dark mode and light mode for your CoreByte experience.</p>

                    <form class="settings-form" method="post">
                        <input type="hidden" name="action" value="update_theme">

                        <div class="settings-row">
                            <label for="theme">Theme Mode</label>
                            <select id="theme" name="theme">
                                <option value="dark" <?= $currentTheme === 'dark' ? 'selected' : '' ?>>Dark Mode</option>
                                <option value="light" <?= $currentTheme === 'light' ? 'selected' : '' ?>>Light Mode</option>
                            </select>
                        </div>

                        <div class="settings-actions">
                            <button type="submit" class="settings-btn primary">Save Theme</button>
                        </div>
                    </form>
                </div>

            <?php elseif ($tab === 'orders'): ?>
                <div class="settings-card">
                    <h2>Order History</h2>
                    <p class="helper">All your past purchases with CoreByte.</p>

                    <?php if (empty($orders)): ?>
                        <p style="color:var(--text-muted);">
                            You haven't placed any orders yet.
                            <a href="Products_Page.php">Browse products →</a>
                        </p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order):
                                        $status = strtolower($order['status'] ?? 'completed');
                                        $badgeClass = match ($status) {
                                            'paid', 'completed' => 'badge success',
                                            'pending' => 'badge pending',
                                            'failed', 'cancelled' => 'badge danger',
                                            default => 'badge',
                                        };
                                    ?>
                                        <tr>
                                            <td>#<?= (int)$order['order_id'] ?></td>
                                            <td class="cell-wrap"><?= htmlspecialchars($order['items'] ?? '—') ?></td>
                                            <td>£<?= number_format((float)$order['total_amount'], 2) ?></td>
                                            <td><span class="<?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($order['status'] ?? '')) ?></span></td>
                                            <td><?= htmlspecialchars(date('d M Y', strtotime($order['created_at']))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($tab === 'transactions'): ?>
                <div class="settings-card">
                    <h2>Transactions</h2>
                    <p class="helper">Your completed payment records.</p>

                    <?php if (empty($transactions)): ?>
                        <p style="color:var(--text-muted);">No transactions found yet.</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Transaction #</th>
                                        <th>Order #</th>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $txn):
                                        $txnStatus = strtolower($txn['transaction_status'] ?? 'successful');
                                        $badgeClass = match ($txnStatus) {
                                            'successful', 'paid', 'completed' => 'badge success',
                                            'pending' => 'badge pending',
                                            'failed', 'cancelled' => 'badge danger',
                                            default => 'badge',
                                        };
                                    ?>
                                        <tr>
                                            <td>#<?= (int)$txn['transaction_id'] ?></td>
                                            <td>#<?= (int)$txn['order_id'] ?></td>
                                            <td><?= htmlspecialchars($txn['payment_method']) ?></td>
                                            <td>£<?= number_format((float)$txn['amount'], 2) ?></td>
                                            <td><span class="<?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($txn['transaction_status'])) ?></span></td>
                                            <td><?= htmlspecialchars(date('d M Y', strtotime($txn['created_at']))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($tab === 'notifications'): ?>
                <div class="settings-card">
                    <h2>Notifications</h2>
                    <p class="helper">Control what emails and alerts you receive.</p>

                    <div class="settings-toggles">
                        <label><input type="checkbox" checked> Order confirmations &amp; receipts</label>
                        <label><input type="checkbox" checked> Deals and weekly offers</label>
                        <label><input type="checkbox"> New game releases</label>
                        <label><input type="checkbox"> Newsletter</label>
                    </div>

                    <div class="settings-actions" style="margin-top:16px;">
                        <button type="button" class="settings-btn primary">Save Preferences</button>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>