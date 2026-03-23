<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('connectdb.php');
require_once('themes.php');

$error_message = '';

/* ---- Auth ---- */
if (!isset($_SESSION['username'])) {
    header("Location: Login_Page.php");
    exit();
}

$is_admin = (int)($_SESSION['is_admin'] ?? 0);
if ($is_admin !== 1) {
    header("Location: home_Page.php");
    exit();
}

/* ---- Handle message delete ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $stmt = $db->prepare("DELETE FROM contact_us WHERE cid = ?");
        $stmt->execute([(int)$_POST['delete_id']]);
        header("Location: Admin_Panel.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

/* ---- Fetch data ---- */
try {
    $users = $db->prepare("SELECT uid, username, email, is_admin FROM users ORDER BY uid ASC");
    $users->execute();
    $users_list = $users->fetchAll(PDO::FETCH_ASSOC);

    $games = $db->prepare("SELECT gid, name, platform, price, age_restriction, discount FROM games ORDER BY gid DESC");
    $games->execute();
    $games_list = $games->fetchAll(PDO::FETCH_ASSOC);

    $msgs = $db->prepare("SELECT cid, full_name, email, subject, message FROM contact_us ORDER BY cid DESC");
    $msgs->execute();
    $contact_messages_list = $msgs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    $error_message = "Database error: " . $ex->getMessage();
    $users_list = $games_list = $contact_messages_list = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/admin_panel.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<main class="admin-wrap">
    <header class="admin-header">
        <h1 class="admin-title">Admin Panel</h1>
        <p class="admin-subtitle">Manage users, games, and messages.</p>
    </header>

    <?php if (!empty($error_message)): ?>
        <div class="settings-alert error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- USERS -->
    <section id="Users-table" class="panel">
        <div class="panel-head">
            <h2>Users <span class="badge"><?= count($users_list) ?></span></h2>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>UID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Admin?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users_list as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$user['uid']) ?></td>
                        <td><?= htmlspecialchars((string)$user['username']) ?></td>
                        <td><?= htmlspecialchars((string)$user['email']) ?></td>
                        <td>
                            <span class="badge <?= ((int)$user['is_admin'] === 1) ? 'success' : '' ?>">
                                <?= ((int)$user['is_admin'] === 1) ? 'Yes' : 'No' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- GAMES -->
    <section id="Games-table" class="panel">
        <div class="panel-head panel-head-row">
            <h2>Games <span class="badge"><?= count($games_list) ?></span></h2>
            <a class="settings-btn primary" href="Add_Game.php">
                + Add Game
            </a>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>GID</th>
                        <th>Name</th>
                        <th>Platform</th>
                        <th>Price (£)</th>
                        <th>Age Rating</th>
                        <th>Discount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($games_list as $game): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$game['gid']) ?></td>
                        <td><?= htmlspecialchars((string)$game['name']) ?></td>
                        <td><?= htmlspecialchars((string)$game['platform']) ?></td>
                        <td>£<?= number_format((float)$game['price'], 2) ?></td>
                        <td><?= htmlspecialchars((string)$game['age_restriction']) ?></td>
                        <td><?= htmlspecialchars((string)$game['discount']) ?>%</td>
                        <td>
                            <a class="settings-btn primary"
                               href="edit_game.php?gid=<?= urlencode((string)$game['gid']) ?>">Edit</a>
                            <button class="settings-btn danger"
                                    data-gid="<?= htmlspecialchars((string)$game['gid']) ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- MESSAGES -->
    <section id="resolved-messages-table" class="panel">
        <div class="panel-head">
            <h2>Messages <span class="badge"><?= count($contact_messages_list) ?></span></h2>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contact_messages_list as $msg): ?>
                    <tr>
                        <td><?= htmlspecialchars($msg['full_name']) ?></td>
                        <td><?= htmlspecialchars($msg['email']) ?></td>
                        <td><?= htmlspecialchars($msg['subject']) ?></td>
                        <td>
                            <?= htmlspecialchars($msg['message']) ?>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="delete_id" value="<?= (int)$msg['cid'] ?>">
                                <button type="submit" class="settings-btn primary">Resolve</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<script>
document.querySelectorAll('button.settings-btn.danger[data-gid]').forEach(btn => {
    btn.addEventListener('click', () => {
        const gid = btn.getAttribute('data-gid');
        if (confirm('Are you sure you want to delete this game? This cannot be undone.')) {
            fetch('delete_game.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'gid=' + encodeURIComponent(gid)
            }).then(() => location.reload());
        }
    });
});
</script>

</body>
</html>