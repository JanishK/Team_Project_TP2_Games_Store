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
    <div class="nav-bar">
        <ul class="nav-left"> 
            <img class="page_logo" src="/Assets/Logo.png" alt="">
            <li><a href="./home_Page.html">Home</a></li>
            <li><a href="./Products_Page.html">Products</a></li>
            <li><a href="./aboutUs_Page.html">About</a></li>
        </ul>

        <ul class="nav-right">
            <li><a href="./contact_us.html"><img src="/Assets/Support.svg" class="basket-icon" alt=""></a></li>
            <li><a href="./registration_page.html"><img src="/Assets/Account.svg" class="basket-icon" alt=""></a></li>
            <li><a href="./basket_Page.html">
                <img src="/Assets/Basket.svg" class="basket-icon" />
            </a></li>
        </ul>
    </div>
    <div>
        <h1>ADMIN Panel</h1>
        <p>Manage the game store settings and inventory from this panel.</p>

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
            <tbody><tr id="user-template" class="row">
                <td>1</td>
                <td>[Username]</td>
                <td>[Email]</td>
                <td>Yes</td>
            </tr></tbody>
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
            <tbody><tr id="user-template" class="row">
                <td>1</td>
                <td>[Game Name]</td>
                <td>[Platform]</td>
                <td>£0.00</td>
                <td>12+</td>
                <td>
                    <button id="edit-button">Edit</button>
                    <button id="delete-button">Delete</button>
                </td>
            </tr></tbody>
        </table>
    </div>
</body>


</html>
