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
if(isset($_POST['delete_id'])){
    $delete_id = $_POST['delete_id'];
    // connect to the database
    require_once('connectdb.php');
    // delete the message with the given id
    try {
        $stmt = $db->prepare("DELETE FROM contact_us WHERE cid = :cid");
        $stmt->bindParam(':cid', $delete_id, PDO::PARAM_INT);
        $stmt->execute();
        // redirect back to admin panel after deletion
        header("Location: Admin_Panel.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
// connect to the database
require_once('connectdb.php');
// Fetch all users from the database
try {
    $contact_messages = $db->prepare("SELECT cid, full_name, email, subject, message FROM contact_us");
    // Execute the query
    $contact_messages ->execute();
    // Fetch all messages
    $contact_messages_list = $contact_messages->fetchAll();

    $users = $db->prepare("SELECT uid, username, email, is_admin FROM users");
    // Execute the query
    $users ->execute();
    // Fetch all users
    $users_list = $users->fetchAll();

    // Fetch all games from the database
    $games = $db->prepare("SELECT gid, name, platform, price, age_restriction, discount FROM games");
    // Execute the query
    $games ->execute();
    // Fetch all games
    $games_list = $games->fetchAll();
}catch (PDOException $ex){
	$error_message = "Sorry, a database error occurred! <br>";
	$error_message = "Error details: <em>". $ex->getMessage()."</em>";
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
        <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>

</head>
<body class="<?php echo $themeClass; ?>">
<?php
require_once('connectdb.php');
require_once('themes.php');
?>

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
                            <th class="col-discount">Discount</th>
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
                                <td><?php echo htmlspecialchars((string)$game['discount']); ?>%</td>
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

        <section id="resolved-messages-table" class="panel">
            <div id="messages-table" class="section">
                <h2>Messages</h2>

                <!-- user messages viewed in table format -->
                <table border="1" class="table">
                    <thead><tr class="row">
                        <th style="width:10%;">Username</th>
                        <th style="width:10%;">Name</th>
                        <th style="width:20%;">Email</th>
                        <th style="width:50%;">Message</th>
                        <th style="width:10%;">Actions</th>
                    </tr></thead>

                    <!-- template row to be modified when displaying messages from the DB -->
                    <tbody>
                        <?php foreach($contact_messages_list as $message): ?>
                        <tr class="row">
                            <td id="name"><?php echo htmlspecialchars($message['full_name']); ?></td>
                            <td id="email"><?php echo htmlspecialchars($message['email']); ?></td>
                            <td id="subject"><?php echo htmlspecialchars($message['subject']); ?></td>
                            <td id="message"><?php echo htmlspecialchars($message['message']); ?></td>
                            <td id=resolve>
                                <form method="post">
                                    <input type="hidden" name="delete_id" value="<?php echo $message['cid']; ?>">
                                    <button type="submit" id="resolve-button" class="btn btn-primary">Resolve</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>
        </section>

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