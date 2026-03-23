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

/* ---- Fetch data ---- */
try {
    $games = $db->prepare("SELECT gid, name, platform, price, age_restriction, discount, stock FROM games ORDER BY name ASC");
    $games->execute();
    $games_list = $games->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    $games_list    = [];
    $error_message = "Database error: " . $ex->getMessage();
}

$total_stock      = array_sum(array_column($games_list, 'stock'));
$low_stock_count  = count(array_filter($games_list, fn($g) => (int)$g['stock'] > 0 && (int)$g['stock'] <= 10));
$out_of_stock_count = count(array_filter($games_list, fn($g) => (int)$g['stock'] === 0));
$in_stock_count   = count($games_list) - $low_stock_count - $out_of_stock_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | CoreByte</title>
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/style.css">
    <link rel="stylesheet" href="/Team_Project_TP2_Games_Store/CSS/Inventory_Management.css">
    <link rel="icon" type="image/png" href="/Team_Project_TP2_Games_Store/Assets/Logo.png">
    <script src="/Team_Project_TP2_Games_Store/JS/app.js" defer></script>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<?php require_once __DIR__ . '/components/navbar.php'; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert error" style="max-width:1400px;margin:16px auto;padding:0 20px;">
        <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>

<!-- DASHBOARD -->
<section id="dashboard">
    <h1>Inventory Dashboard</h1>
    <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">
        Overview of current stock levels and alerts.
    </p>

    <div class="cards">
        <div class="card">
            <h3><?= $total_stock ?></h3>
            <p>Total Units in Stock</p>
        </div>
        <div class="card">
            <h3><?= count($games_list) ?></h3>
            <p>Total Products</p>
        </div>
        <div class="card">
            <h3><?= $low_stock_count ?></h3>
            <p>Low Stock Items</p>
        </div>
        <div class="card">
            <h3><?= $out_of_stock_count ?></h3>
            <p>Out of Stock</p>
        </div>
    </div>

    <div class="toolbar">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <button type="button" onclick="window.print()">Generate Stock Report</button>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <input type="text" id="stockSearch" placeholder="Search products…"
                   oninput="filterStock()" />
            <select id="stockFilter" onchange="filterStock()">
                <option value="">All statuses</option>
                <option value="in">In Stock</option>
                <option value="low">Low Stock</option>
                <option value="out">Out of Stock</option>
            </select>
        </div>
    </div>

    <div class="table-wrapper">
        <table id="stockTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Platform</th>
                    <th>Price (£)</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games_list as $game):
                    $stock = (int)$game['stock'];
                    if ($stock > 10)     { $statusKey = 'in';  $statusLabel = 'In Stock';     $badgeClass = 'badge success'; }
                    elseif ($stock > 0)  { $statusKey = 'low'; $statusLabel = 'Low Stock';    $badgeClass = 'badge pending'; }
                    else                 { $statusKey = 'out'; $statusLabel = 'Out of Stock'; $badgeClass = 'badge danger'; }
                ?>
                <tr data-status="<?= $statusKey ?>"
                    data-name="<?= htmlspecialchars(strtolower($game['name'])) ?>">
                    <td><?= htmlspecialchars($game['name']) ?></td>
                    <td><?= htmlspecialchars($game['platform']) ?></td>
                    <td>£<?= number_format((float)$game['price'], 2) ?></td>
                    <td><?= $stock ?></td>
                    <td><span class="<?= $badgeClass ?>"><?= $statusLabel ?></span></td>
                    <td>
                        <a href="edit_game.php?gid=<?= (int)$game['gid'] ?>"
                           class="settings-btn primary" style="text-decoration:none;display:inline-flex;">
                            Edit
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- INCOMING ORDERS -->
<section id="incoming-orders">
    <h2>Incoming Orders</h2>
    <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">
        Manage supplier deliveries and restocking.
    </p>

    <div class="table-wrapper">
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
                    <td><span class="badge success">In Stock</span></td>
                    <td><button type="button" class="approve">Mark as Received</button></td>
                </tr>
                <tr>
                    <td>1002</td>
                    <td>Nintendo UK</td>
                    <td>2026-03-19</td>
                    <td>Mario Kart 8 Deluxe</td>
                    <td><span class="badge pending">Pending</span></td>
                    <td><button type="button" class="approve">Mark as Received</button></td>
                </tr>
                <tr>
                    <td>1003</td>
                    <td>Nintendo UK</td>
                    <td>2026-03-18</td>
                    <td>ARMS</td>
                    <td><span class="badge pending">Pending</span></td>
                    <td><button type="button" class="approve">Mark as Received</button></td>
                </tr>
                <tr>
                    <td>1004</td>
                    <td>Sony Distribution</td>
                    <td>2026-03-17</td>
                    <td>Grand Theft Auto V (PS5)</td>
                    <td><span class="badge success">In Stock</span></td>
                    <td><button type="button" class="approve">Mark as Received</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<script>
function filterStock() {
    const search = document.getElementById('stockSearch').value.toLowerCase();
    const filter = document.getElementById('stockFilter').value;
    document.querySelectorAll('#stockTable tbody tr').forEach(row => {
        const name   = row.dataset.name   || '';
        const status = row.dataset.status || '';
        const nameOk   = !search || name.includes(search);
        const statusOk = !filter || status === filter;
        row.style.display = (nameOk && statusOk) ? '' : 'none';
    });
}
</script>

</body>
</html>