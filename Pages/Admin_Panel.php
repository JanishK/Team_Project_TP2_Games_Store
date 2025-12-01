<?php
$error_message = '';
$success_message = '';
// Start the session
session_start();
// Get is_admin from session
$is_admin = $_SESSION['is_admin'] ?? false;
// Check if user is admin
if($is_admin !== 1){
    // Redirect to home page if not admin
    header("Location: Home_Page.html");
    exit();
}
 // if not logged in, redirect to login page
if(!isset($_SESSION['username'])){
    // Redirect to login page
    header("Location: Login_Page.php");
    exit();
}
// connect to the database
require_once('connectdb.php');
// Fetch all users from the database
try {
    $users = $db->prepare("SELECT uid, username, email, is_admin FROM users");
    // Execute the query
    $users ->execute();
    // Fetch all users
    $users_list = $users->fetchAll();
    // Fetch all games from the database
    $games = $db->prepare("SELECT gid, name, platform, price, age_restriction FROM games");
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
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="icon" type="image/png" href="/Assets/Logo.png">

    <!-- internal css for webpage layout -->
    <style>
        .table{ 
            width: 95%; 
            margin: auto; 
        }

        button { 
            padding: 5px;
            background-color: purple;
        }

        #add-button   { margin: 0 0 20px 2.5%; }
        #delete-button{ background-color: red; }
        .section      { margin-top: 50px; }
        h2            { margin: 20px 20px 20px 1%; }
        th,td         { padding: 10px; }
    </style>
</head>
<body>
    <!-- navigation bar to link to other webpages -->
    <div class="nav-bar">
        <ul class="nav-left"> 
            <img class="page_logo" src="/Assets/Logo.png" alt="">
            <li><a href="./home_Page.html">Home</a></li>
            <li><a href="./Products_Page.html">Products</a></li>
            <li><a href="./aboutUs_Page.html">About</a></li>
        </ul>

        <ul class="nav-right">
            <li><a href="./contact_us.html"><img src="/Assets/Support.svg" class="basket-icon" alt=""></a></li>
            <li><a href="./registration_page.php"><img src="/Assets/Account.svg" class="basket-icon" alt=""></a></li>
            <li><a href="./basket_Page.html">
                <img src="/Assets/Basket.svg" class="basket-icon" />
            </a></li>
        </ul>
    </div>

    <!-- div section displaying all the registered users -->
    <div id="Users-table" class="section">
        <h2>Users</h2>

        <!-- users viewed in a table format -->
        <table border="1" class="table">
            <thead><tr class="row">
                <th style="width:5%;">UID</th>
                <th style="width:45%;">Username</th>
                <th style="width:45%;">Email</th>
                <th style="width:5%;">Admin?</th>
            </tr></thead>

            <!-- template row to be modified when displaying users from the DB -->
            <tbody>
                <?php foreach($users_list as $user): ?>
                <tr id="user-template" class="row">
                    <td><?php echo htmlspecialchars($user['uid']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                    
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>

    <!-- div section displaying all the published games -->
    <div id="Games-table" class="section">
        <h2>Games</h2>

        <!-- button to publish a game -->
        <a href="Add_Game.php"><button id="add-button">Add New Game</button></a>

        <!-- games viewed in a table format -->
        <table border="1" class="table">
            <thead><tr class="row">
                <th style="width:5%;">GID</th>
                <th style="width:55%;">Name</th>
                <th style="width:10%;">Platform</th>
                <th style="width:10%;">Price(£)</th>
                <th style="width:10%;">Age Rating</th>
                <th style="width:10%;">Actions</th>
            </tr></thead>

            <!-- template row to be modified when displaying games from the DB -->
            <tbody>
                <?php foreach($games_list as $game): ?>
                <tr class="row">
                    <td><?php echo htmlspecialchars($game['gid']); ?></td>
                    <td><?php echo htmlspecialchars($game['name']); ?></td>
                    <td><?php echo htmlspecialchars($game['platform']); ?></td>
                    <td><?php echo htmlspecialchars($game['price']); ?></td>
                    <td><?php echo htmlspecialchars($game['age_restriction']); ?></td>
                <td>
                    <a href= "edit_game.php?gid=<?= $game['gid']?>">
                        <button id="edit-button">Edit</button></a>
                    <button id="delete-button">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>
</body>


</html>