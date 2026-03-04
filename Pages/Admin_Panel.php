<?php
declare(strict_types=1);
session_start();

$error_message = "";

// Auth checks
if (!isset($_SESSION['username'])) {
    header("Location: Login_Page.php");
    exit();
}

$is_admin = (int)($_SESSION['is_admin'] ?? 0);
if ($is_admin !== 1) {
    header("Location: home_Page.html"); // make sure this exists
    exit();
}

// Includes (load theme before output)
require_once __DIR__ . '/themes.php';
require_once __DIR__ . '/connectdb.php';

// Default theme if not set by themes.php
$themeClass = $themeClass ?? "theme-dark";

try {
    $usersStmt = $db->prepare("SELECT uid, username, email, is_admin FROM users ORDER BY uid ASC");
    $usersStmt->execute();
    $users_list = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

    $gamesStmt = $db->prepare("SELECT gid, name, platform, price, age_restriction FROM games ORDER BY gid ASC");
    $gamesStmt->execute();
    $games_list = $gamesStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {
    $error_message = "Sorry, a database error occurred! Error details: " . $ex->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>

    <!-- existing global styles if you want -->
    <link rel="stylesheet" href="../CSS/style.css">

    <!-- NEW: admin panel styles -->
    <link rel="stylesheet" href="../CSS/admin_panel.css">

    <link rel="icon" type="image/png" href="../Assets/Logo.png">
</head>

<body class="<?php echo htmlspecialchars($themeClass); ?>">
    <!-- nav -->
    <?php require_once __DIR__ . '/components/navbar.php'; ?>


    <main class="admin-wrap">
        <header class="admin-header">
            <h1 class="admin-title">Admin Panel</h1>
            <p class="admin-subtitle">Manage users, games, and messages.</p>
        </header>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- USERS -->
        <section id="Users-table" class="panel">
            <div class="panel-head">
                <h2>Users</h2>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="col-uid">UID</th>
                            <th class="col-username">Username</th>
                            <th class="col-email">Email</th>
                            <th class="col-admin">Admin?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users_list as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string)$user['uid']); ?></td>
                                <td><?php echo htmlspecialchars((string)$user['username']); ?></td>
                                <td><?php echo htmlspecialchars((string)$user['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo ((int)$user['is_admin'] === 1) ? 'badge-yes' : 'badge-no'; ?>">
                                        <?php echo ((int)$user['is_admin'] === 1) ? 'Yes' : 'No'; ?>
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
                <h2>Games</h2>
                <a class="btn btn-primary" href="Add_Game.php">Add New Game</a>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="col-gid">GID</th>
                            <th class="col-name">Name</th>
                            <th class="col-platform">Platform</th>
                            <th class="col-price">Price (£)</th>
                            <th class="col-age">Age Rating</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($games_list as $game): ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string)$game['gid']); ?></td>
                                <td><?php echo htmlspecialchars((string)$game['name']); ?></td>
                                <td><?php echo htmlspecialchars((string)$game['platform']); ?></td>
                                <td><?php echo htmlspecialchars((string)$game['price']); ?></td>
                                <td><?php echo htmlspecialchars((string)$game['age_restriction']); ?></td>
                                <td class="actions">
                                    <a class="btn btn-secondary" href="edit_game.php?gid=<?php echo urlencode((string)$game['gid']); ?>">Edit</a>
                                    <button class="btn btn-danger" type="button" data-gid="<?php echo htmlspecialchars((string)$game['gid']); ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- MESSAGES (placeholder) -->
        <section id="messages-table" class="panel">
            <div class="panel-head">
                <h2>Messages</h2>
            </div>

            <div class="empty-state">
                <p>This section is currently a placeholder. Connect it to your messages table when ready.</p>
            </div>

            <a class="btn btn-ghost" href="#">View Previously Resolved Messages</a>
        </section>

        <!-- RESOLVED (placeholder) -->
        <section id="resolved-messages-table" class="panel">
            <div class="panel-head">
                <h2>Previously Resolved Messages</h2>
            </div>

            <div class="empty-state">
                <p>This section is currently a placeholder.</p>
            </div>
        </section>
    </main>

    <script>
      // Optional: wire up delete buttons later (AJAX)
      document.querySelectorAll('button.btn-danger[data-gid]').forEach(btn => {
        btn.addEventListener('click', () => {
          const gid = btn.getAttribute('data-gid');
          alert("Hook delete logic for GID: " + gid);
        });
      });
    </script>
</body>
</html>