<?php
session_start();

$error_message = "";

// Auth checks
if (!isset($_SESSION['username'])) {
    header("Location: Login_Page.php");
    exit();
}

$is_admin = (int) ($_SESSION['is_admin'] ?? 0);
if ($is_admin !== 1) {
    header("Location: home_Page.html"); // make sure this exists
    exit();
}
if (isset($_POST['delete_id'])) {
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
    $contact_messages->execute();
    // Fetch all messages
    $contact_messages_list = $contact_messages->fetchAll();

    $users = $db->prepare("SELECT uid, username, email, is_admin FROM users");
    // Execute the query
    $users->execute();
    // Fetch all users
    $users_list = $users->fetchAll();

    // Fetch all games from the database
    $games = $db->prepare("SELECT gid, name, platform, price, age_restriction, discount, stock FROM games");
    // Execute the query
    $games->execute();
    // Fetch all games
    $games_list = $games->fetchAll();
} catch (PDOException $ex) {
    $error_message = "Sorry, a database error occurred! <br>";
    $error_message = "Error details: <em>" . $ex->getMessage() . "</em>";
}
$total_stock = array_sum(array_column($games_list, 'stock'));
$low_stock_count = count(array_filter($games_list, fn($game) => (int) $game['stock'] > 0 && (int) $game['stock'] <= 10));
$out_of_stock_count = count(array_filter($games_list, fn($game) => (int) $game['stock'] === 0));
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
    <link rel="stylesheet" href="../CSS/Inventory_Management.css">

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

        <!-- the code for the main webpage -->
        <div class="container">
            <main>

                <!-- dashboard section -->
                <section id="dashboard">
                    <h2>Inventory Dashboard</h2>

                    <!-- overview of current stock levels -->
                    <p><small>Overview of current stock levels and alerts.</small></p>
                    <div class="cards">
                        <div class="card">
                            <h3>Total Products</h3>
                            <p><?php echo $total_stock; ?></p>
                        </div>
                        <div class="card">
                            <h3>Low Stock Items</h3>
                            <p class="status-low"><?php echo $low_stock_count; ?></p>
                        </div>
                        <div class="card">
                            <h3>Out of Stock Items</h3>
                            <p class="status-out"><?php echo $out_of_stock_count; ?></p>
                        </div>
                        <div class="card">
                            <h3>Incoming Orders</h3>
                            <p>4</p>
                        </div>
                    </div>

                    <!-- stock reports and product filter -->
                    <div class="toolbar">
                        <div>
                            <button class="stockReportButton">Generate Stock Report</button>
                        </div>
                        <div>
                            <input type="text" placeholder="Search products...">
                            <select>
                                <option value="">Filter by status</option>
                                <option>In Stock</option>
                                <option>Low Stock</option>
                                <option>Out of Stock</option>
                            </select>
                        </div>
                    </div>

                    <!-- table to view products stock information -->
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price (£)</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- placeholder template row -->
                            <!-- use id's to update cells from database -->
                            <tr>
                                <?php foreach ($games_list as $game): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string) $game['name']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $game['price']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $game['stock']); ?></td>
                                    <td>
                                        <?php
                                        $stock = (int) $game['stock'];
                                        if ($stock > 10) {
                                            echo '<span class="status-in">In Stock</span>';
                                        } elseif ($stock > 0) {
                                            echo '<span class="status-low">Low Stock</span>';
                                        } else {
                                            echo '<span class="status-out">Out of Stock</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="actions">
                                        <button class="btn-link">Adjust Stock</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Incoming Orders -->
                </td>
                </tr>
                </tbody>
                </table>
                </section>

                <!-- Incoming Orders -->
                <section id="incoming-orders">
                    <h2>Incoming Orders</h2>
                    <p><small>Manage supplier deliveries and restocking.</small></p>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Supplier</th>
                                <th>Expected Date</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1001</td>
                                <td>Nintendo UK</td>
                                <td>2026-03-20</td>
                                <td>The Legend of Zelda: Breath of the Wild</td>
                                <td class="status-in">In Stock</td>
                                <td><button class="btn-primary">Mark as Received</button></td>
                            </tr>

                            <tr>
                                <td>1002</td>
                                <td>Nintendo UK</td>
                                <td>2026-03-19</td>
                                <td>Mario Kart 8 Deluxe</td>
                                <td class="status-in">In Stock</td>
                                <td><button class="btn-primary">Mark as Received</button></td>
                            </tr>

                            <tr>
                                <td>1003</td>
                                <td>Nintendo UK</td>
                                <td>2026-03-18</td>
                                <td>ARMS</td>
                                <td class="status-in">In Stock</td>
                                <td><button class="btn-primary">Mark as Received</button></td>
                            </tr>

                            <tr>
                                <td>1004</td>
                                <td>Sony Distribution</td>
                                <td>2026-03-17</td>
                                <td>Grand Theft Auto V (PS5)</td>
                                <td class="status-in">In Stock</td>
                                <td><button class="btn-primary">Mark as Received</button></td>
                            </tr>

                        </tbody>
                    </table>
                </section>

            </main>
        </div>

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