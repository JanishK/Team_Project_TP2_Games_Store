<?php
session_start();
require_once('connectdb.php');

if (!isset($_SESSION["username"])) {
    header("Location: Login_Page.php");
    exit();
}

$username = $_SESSION["username"];
$success = "";
$error = "";

/*
|--------------------------------------------------------------------------
| CSRF token
|--------------------------------------------------------------------------
*/
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

/*
|--------------------------------------------------------------------------
| Ensure settings row exists
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| Default settings
|--------------------------------------------------------------------------
*/
$settings = [
    "display_name" => null,
    "theme" => "dark",
    "email_notifications" => 1,
    "marketing_emails" => 0,
    "currency" => "GBP",
    "language" => "en",
    "profile_image" => null
];

/*
|--------------------------------------------------------------------------
| Load settings
|--------------------------------------------------------------------------
*/
try {
    $get = $db->prepare("SELECT * FROM user_settings WHERE username = ? LIMIT 1");
    $get->execute([$username]);
    $row = $get->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $settings = array_merge($settings, $row);
    }
} catch (PDOException $ex) {
    if (!$error) {
        $error = "Failed to load settings.";
    }
}

/*
|--------------------------------------------------------------------------
| Theme class
|--------------------------------------------------------------------------
*/
$themeClass = (($settings["theme"] ?? "dark") === "light") ? "theme-light" : "theme-dark";

/*
|--------------------------------------------------------------------------
| Section
|--------------------------------------------------------------------------
*/
$section = $_GET['section'] ?? 'profile';
$allowedSections = ['profile', 'preferences', 'orders', 'security'];

if (!in_array($section, $allowedSections, true)) {
    $section = 'profile';
}

/*
|--------------------------------------------------------------------------
| Handle POST actions
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Security check failed. Please refresh and try again.";
    } else {
        $action = $_POST['action'] ?? '';

        /*
        |--------------------------------------------------------------------------
        | Save profile/preferences
        |--------------------------------------------------------------------------
        */
        if ($action === 'save_settings') {
            $display_name = trim($_POST["display_name"] ?? "");
            $theme = (($_POST["theme"] ?? "dark") === "light") ? "light" : "dark";

            $email_notifications = isset($_POST["email_notifications"]) ? 1 : 0;
            $marketing_emails = isset($_POST["marketing_emails"]) ? 1 : 0;

            $currency = $_POST["currency"] ?? "GBP";
            $allowedCurrencies = ["GBP", "USD", "EUR"];
            if (!in_array($currency, $allowedCurrencies, true)) {
                $currency = "GBP";
            }

            $language = $_POST["language"] ?? "en";
            $allowedLanguages = ["en", "ne", "hi"];
            if (!in_array($language, $allowedLanguages, true)) {
                $language = "en";
            }

            try {
                $update = $db->prepare("
                    UPDATE user_settings
                    SET
                        display_name = ?,
                        theme = ?,
                        email_notifications = ?,
                        marketing_emails = ?,
                        currency = ?,
                        language = ?
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

                $settings["display_name"] = $display_name !== "" ? $display_name : null;
                $settings["theme"] = $theme;
                $settings["email_notifications"] = $email_notifications;
                $settings["marketing_emails"] = $marketing_emails;
                $settings["currency"] = $currency;
                $settings["language"] = $language;

                $themeClass = $theme === "light" ? "theme-light" : "theme-dark";
                $success = "Settings updated successfully!";
            } catch (PDOException $ex) {
                $error = "Failed to save settings. Please try again.";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Upload avatar
        |--------------------------------------------------------------------------
        */
        if ($action === 'upload_avatar') {
            if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
                $error = "Please choose an image to upload.";
            } else {
                $file = $_FILES['profile_image'];
                $maxBytes = 2 * 1024 * 1024;

                if ($file['size'] > $maxBytes) {
                    $error = "Image is too large (max 2MB).";
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file['tmp_name']);

                    $allowed = [
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/webp' => 'webp'
                    ];

                    if (!isset($allowed[$mime])) {
                        $error = "Only JPG, PNG, or WEBP images are allowed.";
                    } else {
                        $ext = $allowed[$mime];
                        $safeUser = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $username);
                        $newName = $safeUser . "_" . bin2hex(random_bytes(8)) . "." . $ext;

                        $projectWebRoot = "/Team_Project_TP2_Games_Store";
                        $uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $projectWebRoot . "/uploads/avatars";

                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        $dest = $uploadDir . "/" . $newName;

                        if (!move_uploaded_file($file['tmp_name'], $dest)) {
                            $error = "Upload failed. Please try again.";
                        } else {
                            $webPath = $projectWebRoot . "/uploads/avatars/" . $newName;

                            try {
                                $up = $db->prepare("UPDATE user_settings SET profile_image = ? WHERE username = ?");
                                $up->execute([$webPath, $username]);

                                $settings["profile_image"] = $webPath;
                                $_SESSION["profile_image"] = $webPath;
                                $success = "Profile image updated!";
                            } catch (PDOException $ex) {
                                $error = "Saved image, but failed to update profile.";
                            }
                        }
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Change password
        |--------------------------------------------------------------------------
        */
        if ($action === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new1 = $_POST['new_password'] ?? '';
            $new2 = $_POST['confirm_password'] ?? '';

            if ($new1 === '' || strlen($new1) < 8) {
                $error = "New password must be at least 8 characters.";
            } elseif ($new1 !== $new2) {
                $error = "New passwords do not match.";
            } else {
                try {
                    $q = $db->prepare("SELECT password_hash FROM users WHERE username = ? LIMIT 1");
                    $q->execute([$username]);
                    $u = $q->fetch(PDO::FETCH_ASSOC);

                    if (!$u || !password_verify($current, $u['password_hash'])) {
                        $error = "Current password is incorrect.";
                    } else {
                        $hash = password_hash($new1, PASSWORD_BCRYPT);
                        $upd = $db->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
                        $upd->execute([$hash, $username]);
                        $success = "Password changed successfully!";
                    }
                } catch (PDOException $ex) {
                    $error = "Could not change password right now.";
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Load orders
|--------------------------------------------------------------------------
| This version uses username because your page already uses username everywhere.
| It also safely handles different possible column names.
|--------------------------------------------------------------------------
*/
$orders = [];

try {
    $orderColumns = [];
    $desc = $db->query("DESCRIBE orders");
    $columns = $desc->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        $orderColumns[] = $col['Field'];
    }

    $amountColumn = null;
    if (in_array('total', $orderColumns, true)) {
        $amountColumn = 'total';
    } elseif (in_array('total_amount', $orderColumns, true)) {
        $amountColumn = 'total_amount';
    } elseif (in_array('subtotal', $orderColumns, true)) {
        $amountColumn = 'subtotal';
    }

    $hasPaymentMethod = in_array('payment_method', $orderColumns, true);
    $hasUsername = in_array('username', $orderColumns, true);

    if ($hasUsername && $amountColumn) {
        $selectPayment = $hasPaymentMethod ? "payment_method" : "'N/A' AS payment_method";

        $sql = "
            SELECT
                order_id,
                $selectPayment,
                $amountColumn AS order_total,
                status,
                created_at
            FROM orders
            WHERE username = ?
            ORDER BY created_at DESC
            LIMIT 20
        ";

        $o = $db->prepare($sql);
        $o->execute([$username]);
        $orders = $o->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
} catch (PDOException $ex) {
    // Keep orders empty if schema doesn't match yet
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings</title>

    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css?v=dev">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/settings.css?v=dev2">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png" />

    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.css">
    <script defer src="/Team_Project_TP2_Games_Store/Assets/ChatBot/chatbot.js"></script>

    <style>
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: 18px;
        }

        .order-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 16px;
        }

        .order-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .order-meta span {
            opacity: 0.8;
            font-size: 0.92rem;
        }

        .order-right {
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            margin-top: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            background: rgba(124, 92, 255, 0.16);
            color: #cbbcff;
        }

        .helper {
            opacity: .8;
            margin-bottom: 16px;
        }

        @media (max-width: 700px) {
            .order-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-right {
                text-align: left;
            }
        }
    </style>
</head>

<body class="<?php echo htmlspecialchars($themeClass); ?>">

    <?php require_once __DIR__ . '/components/navbar.php'; ?>

    <main class="settings-container">
        <div class="settings-header">
            <div>
                <h1 class="settings-title">Account Settings</h1>
                <p class="settings-subtitle">Manage your profile, preferences, orders, and security.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="settings-alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="settings-alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="settings-grid">
            <aside class="settings-sidebar">
                <nav class="settings-nav">
                    <a class="<?php echo $section === 'profile' ? 'active' : ''; ?>" href="?section=profile">Profile</a>
                    <a class="<?php echo $section === 'preferences' ? 'active' : ''; ?>" href="?section=preferences">Preferences</a>
                    <a class="<?php echo $section === 'orders' ? 'active' : ''; ?>" href="?section=orders">Orders</a>
                    <a class="<?php echo $section === 'security' ? 'active' : ''; ?>" href="?section=security">Security</a>
                </nav>
            </aside>

            <section>
                <?php if ($section === 'profile'): ?>
                    <div class="settings-card">
                        <h2>Profile</h2>
                        <p class="helper">Update your display name and profile image.</p>

                        <div class="profile-row">
                            <?php $avatar = $settings["profile_image"] ?: "/Team_Project_TP2_Games_Store/Assets/default-avatar.png"; ?>
                            <img class="profile-avatar" src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile image" />
                            <div>
                                <div style="font-weight:600;">
                                    <?php echo htmlspecialchars($settings["display_name"] ?: $username); ?>
                                </div>
                                <div style="opacity:.8;font-size:13px;">
                                    @<?php echo htmlspecialchars($username); ?>
                                </div>
                            </div>
                        </div>

                        <div style="height:12px"></div>

                        <form class="settings-form" method="POST" action="?section=profile">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                            <input type="hidden" name="action" value="save_settings">

                            <div class="settings-row">
                                <label for="display_name">Display name</label>
                                <input
                                    type="text"
                                    id="display_name"
                                    name="display_name"
                                    placeholder="e.g., Janish"
                                    value="<?php echo htmlspecialchars($settings['display_name'] ?? ''); ?>"
                                />
                            </div>

                            <div class="settings-actions">
                                <button class="settings-btn primary" type="submit">Save</button>
                            </div>
                        </form>

                        <div style="height:16px"></div>

                        <form class="settings-form" method="POST" action="?section=profile" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                            <input type="hidden" name="action" value="upload_avatar">

                            <div class="settings-row">
                                <label for="profile_image">Profile image</label>
                                <input id="profile_image" type="file" name="profile_image" accept="image/png,image/jpeg,image/webp" />
                            </div>

                            <div class="settings-actions">
                                <button class="settings-btn" type="submit">Upload image</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <?php if ($section === 'preferences'): ?>
                    <div class="settings-card">
                        <h2>Preferences</h2>
                        <p class="helper">Theme, currency, language, and notifications.</p>

                        <form class="settings-form" method="POST" action="?section=preferences">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                            <input type="hidden" name="action" value="save_settings">

                            <div class="settings-row">
                                <label for="theme">Theme</label>
                                <select id="theme" name="theme">
                                    <option value="dark" <?php echo ($settings["theme"] === "dark") ? "selected" : ""; ?>>Dark</option>
                                    <option value="light" <?php echo ($settings["theme"] === "light") ? "selected" : ""; ?>>Light</option>
                                </select>
                            </div>

                            <div class="settings-row">
                                <label for="currency">Currency</label>
                                <select id="currency" name="currency">
                                    <option value="GBP" <?php echo ($settings["currency"] === "GBP") ? "selected" : ""; ?>>GBP (£)</option>
                                    <option value="USD" <?php echo ($settings["currency"] === "USD") ? "selected" : ""; ?>>USD ($)</option>
                                    <option value="EUR" <?php echo ($settings["currency"] === "EUR") ? "selected" : ""; ?>>EUR (€)</option>
                                </select>
                            </div>

                            <div class="settings-row">
                                <label for="language">Language</label>
                                <select id="language" name="language">
                                    <option value="en" <?php echo ($settings["language"] === "en") ? "selected" : ""; ?>>English</option>
                                    <option value="ne" <?php echo ($settings["language"] === "ne") ? "selected" : ""; ?>>Nepali</option>
                                    <option value="hi" <?php echo ($settings["language"] === "hi") ? "selected" : ""; ?>>Hindi</option>
                                </select>
                            </div>

                            <div class="settings-toggles">
                                <label>
                                    <input type="checkbox" name="email_notifications" <?php echo ($settings["email_notifications"] ? "checked" : ""); ?> />
                                    Email notifications (orders, delivery updates)
                                </label>

                                <label>
                                    <input type="checkbox" name="marketing_emails" <?php echo ($settings["marketing_emails"] ? "checked" : ""); ?> />
                                    Marketing emails (deals & promos)
                                </label>
                            </div>

                            <div class="settings-actions">
                                <button class="settings-btn primary" type="submit">Save preferences</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <?php if ($section === 'orders'): ?>
                    <div class="settings-card">
                        <h2>Orders & Transactions</h2>
                        <p class="helper">View your recent purchases and payment records.</p>

                        <?php if (!empty($orders)): ?>
                            <div class="orders-list">
                                <?php foreach ($orders as $order): ?>
                                    <div class="order-row">
                                        <div class="order-meta">
                                            <strong>Order #<?php echo (int)$order['order_id']; ?></strong>
                                            <span>Payment: <?php echo htmlspecialchars($order['payment_method']); ?></span>
                                            <span><?php echo htmlspecialchars($order['created_at']); ?></span>
                                        </div>

                                        <div class="order-right">
                                            <strong>£<?php echo number_format((float)$order['order_total'], 2); ?></strong><br>
                                            <span class="status-badge"><?php echo htmlspecialchars($order['status']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No transactions yet.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($section === 'security'): ?>
                    <div class="settings-card">
                        <h2>Security</h2>
                        <p class="helper">Change your password and keep your account safe.</p>

                        <form class="settings-form" method="POST" action="?section=security">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                            <input type="hidden" name="action" value="change_password">

                            <div class="settings-row">
                                <label for="current_password">Current password</label>
                                <input id="current_password" type="password" name="current_password" />
                            </div>

                            <div class="settings-row">
                                <label for="new_password">New password</label>
                                <input id="new_password" type="password" name="new_password" />
                            </div>

                            <div class="settings-row">
                                <label for="confirm_password">Confirm new password</label>
                                <input id="confirm_password" type="password" name="confirm_password" />
                            </div>

                            <div class="settings-actions">
                                <button class="settings-btn primary" type="submit">Change password</button>
                            </div>
                        </form>

                        <div style="height:14px"></div>

                        <div class="settings-actions">
                            <form method="POST" action="logout.php">
                                <button class="settings-btn danger" type="submit">Log out</button>
                            </form>

                            <button class="settings-btn" type="button" onclick="alert('Implement session tokens to enable logout-all-devices.')">
                                Log out all devices
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
</body>
</html>