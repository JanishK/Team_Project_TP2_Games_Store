<?php
session_start();
require_once('connectdb.php');

// Is the user logged in?
$logged_in = isset($_SESSION['username']);

// Helper: get gid for a game by its DB name
function getGameIdByName(PDO $db, string $name): int {
    $stmt = $db->prepare("SELECT gid FROM games WHERE name = ?");
    $stmt->execute([$name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['gid'] : 0;
}

/*
    IMPORTANT: These names MUST match exactly the `name` column
    in my `games` table (from my SQL dump):

    3  - The Legend of Zelda: Breath of the Wild (Nintendo Switch)
    4  - Mario Kart 8 Deluxe (Nintendo Switch)
    6  - Grand Theft Auto V (PS5)
    15 - Grand Theft Auto VI: Standard Edition PS5
    16 - Elden Ring: Standard Edition PS5

    For Forza, Halo, Cyberpunk: gid will be 0 unless you add those rows.
    (Add them via Admin and make sure the names match below.)
*/

// Map each card to its DB game name (for gid lookup)
$gta6Id      = getGameIdByName($db, 'Grand Theft Auto VI: Standard Edition PS5');
$eldenPs5Id  = getGameIdByName($db, 'Elden Ring: Standard Edition PS5');
$gta5Ps5Id   = getGameIdByName($db, 'Grand Theft Auto V (PS5)');
$forzaId     = getGameIdByName($db, 'Forza Horizon 5'); 
$haloId      = getGameIdByName($db, 'Halo Infinite');    
$cyberpunkId = getGameIdByName($db, 'Cyberpunk 2077');   
$zeldaId     = getGameIdByName($db, 'The Legend of Zelda: Breath of the Wild (Nintendo Switch)');
$marioId     = getGameIdByName($db, 'Mario Kart 8 Deluxe (Nintendo Switch)');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreByte | Products</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="icon" type="image/png" href="../Assets/Logo.png">
</head>
<body>

<!-- NAVBAR -->
<div class="nav-bar">
    <ul class="nav-left">
        <img class="page_logo" src="../Assets/Logo.png" alt="">
        <li><a href="./home_Page.html">Home</a></li>
        <li><a href="./Products_Page.php">Products</a></li>
        <li><a href="./aboutUs_Page.html">About</a></li>
    </ul>
    <ul class="nav-right">
        <li><a href="./contactUs_Page.html"><img src="../Assets/Support.svg" class="basket-icon" alt=""></a></li>
        <li><a href="./Login_Page.php"><img src="../Assets/Account.svg" class="basket-icon" alt=""></a></li>
        <li><a href="./basket_Page.php">
            <img src="../Assets/Basket.svg" class="basket-icon" />
        </a></li>
    </ul>
</div>

<div class="page-name">
    <h1>Products</h1>
    <p>Explore our extensive collection of games available for purchase.</p>
</div>

<div class="products-page-wrapper">

    <!-- FILTER SIDEBAR -->
    <aside class="filter-bar">
        <h2>Filter</h2>

        <label for="searchInput">Search for your favorite games</label><br>
        <input type="text" id="searchInput" placeholder="Search..."><br><br>

        <label for="platformSelect">Platform</label><br>
        <select id="platformSelect">
            <option>All Platforms</option>
            <option>PlayStation</option>
            <option>Xbox</option>
            <option>PC</option>
            <option>Nintendo</option>
        </select><br><br>

        <label for="genreSelect">Genre</label><br>
        <select id="genreSelect">
            <option>All Genres</option>
            <option>Action</option>
            <option>Adventure</option>
            <option>RPG</option>
            <option>Shooter</option>
            <option>Racing</option>
        </select><br><br>

        <button onclick="applyFilters()">Apply Filters</button>
    </aside>

    <!-- PRODUCT GRID -->
    <section class="main-content-wrapper">

        <!-- PRODUCT 1: GTA VI -->
        <div class="product"
             data-name="Grand Theft Auto VI: Standard Edition PS5"
             data-platform="PlayStation"
             data-genre="Action, Adventure">
            <p>STANDARD EDITION</p>
            <img src="../Assets/Game_Images/GTA VI.png" alt="GTA VI">
            <h3>GRAND THEFT AUTO VI: STANDARD EDITION PS5</h3>
            <p>£69.99</p>
            <button onclick="openProduct(
                'Grand Theft Auto VI: Standard Edition PS5',
                '../Assets/Game_Images/GTA VI.png',
                'Grand Theft Auto VI delivers a next-generation open world with dynamic AI, missions, and immersive gameplay.',
                '£69.99',
                'PlayStation',
                '18+',
                <?php echo $gta6Id; ?>
            )">View Details</button>
        </div>

        <!-- PRODUCT 2: ELDEN RING PS5 -->
        <div class="product"
             data-name="Elden Ring: Standard Edition PS5"
             data-platform="PlayStation"
             data-genre="Action, RPG">
            <p>STANDARD EDITION</p>
            <img src="../Assets/Game_Images/Elden_Ring.jpg" alt="Elden Ring">
            <h3>ELDEN RING: STANDARD EDITION PS5</h3>
            <p>£54.99</p>
            <button onclick="openProduct(
                'Elden Ring: Standard Edition PS5',
                '../Assets/Game_Images/Elden_Ring.jpg',
                'Elden Ring is an award-winning open-world RPG set in the Lands Between with deep combat and exploration.',
                '£54.99',
                'PlayStation',
                '16+',
                <?php echo $eldenPs5Id; ?>
            )">View Details</button>
        </div>

        <!-- PRODUCT 3: GTA V PS5 -->
        <div class="product"
             data-name="Grand Theft Auto V: Standard Edition PS5"
             data-platform="PlayStation"
             data-genre="Action, Adventure">
            <p>ADULT EDITION</p>
            <img src="../Assets/Game_Images/GTA_V.jpg" alt="GTA V">
            <h3>GRAND THEFT AUTO V: STANDARD EDITION PS5</h3>
            <p>£39.99</p>
            <button onclick="openProduct(
                'Grand Theft Auto V: Standard Edition PS5',
                '../Assets/Game_Images/GTA_V.jpg',
                'Grand Theft Auto V includes enhanced graphics, new features, and GTA Online support.',
                '£39.99',
                'PlayStation',
                '18+',
                <?php echo $gta5Ps5Id; ?>
            )">View Details</button>
        </div>

        <!-- PRODUCT 4: FORZA HORIZON 5 (requires DB row to work fully) -->
        <div class="product"
             data-name="Forza Horizon 5"
             data-platform="Xbox"
             data-genre="Racing">
            <p>STANDARD EDITION</p>
            <img src="../Assets/Game_Images/Forza_Horizon_5.jpg" alt="Forza Horizon 5">
            <h3>FORZA HORIZON 5</h3>
            <p>£49.99</p>
            <button onclick="openProduct(
                'Forza Horizon 5',
                '../Assets/Game_Images/Forza_Horizon_5.jpg',
                'Forza Horizon 5 is an open-world racing game set in Mexico with hundreds of customizable cars.',
                '£49.99',
                'Xbox',
                '3+',
                <?php echo $forzaId; ?>
            )">View Details</button>
        </div>

        <!-- PRODUCT 5: HALO INFINITE (requires DB row) -->
        <div class="product"
             data-name="Halo Infinite"
             data-platform="Xbox"
             data-genre="Shooter">
            <p>STANDARD EDITION</p>
            <img src="../Assets/Game_Images/Halo_Infinite.jpg" alt="Halo Infinite">
            <h3>HALO INFINITE</h3>
            <p>£59.99</p>
            <button onclick="openProduct(
                'Halo Infinite',
                '../Assets/Game_Images/Halo_Infinite.jpg',
                'Halo Infinite brings Master Chief back in a massive semi-open world with intense sci-fi combat.',
                '£59.99',
                'Xbox',
                '16+',
                <?php echo $haloId; ?>
            )">View Details</button>
        </div>

        <!-- PRODUCT 6: CYBERPUNK 2077 (requires DB row) -->
        <div class="product"
             data-name="Cyberpunk 2077"
             data-platform="PC"
             data-genre="RPG">
            <p>STANDARD EDITION</p>
            <img src="../Assets/Game_Images/Cyberpunk_2077.jpg" alt="Cyberpunk 2077">
            <h3>CYBERPUNK 2077</h3>
            <p>£44.99</p>
            <button onclick="openProduct(
                'Cyberpunk 2077',
                '../Assets/Game_Images/Cyberpunk_2077.jpg',
                'Cyberpunk 2077 is a futuristic RPG set in Night City, full of open-world exploration and deep story choices.',
                '£44.99',
                'PC',
                '18+',
                <?php echo $cyberpunkId; ?>
            )">View Details</button>
        </div>

        <!-- PRODUCT 7: ZELDA (uses BotW DB row) -->
        <div class="product"
             data-name="The Legend of Zelda: Breath of the Wild"
             data-platform="Nintendo"
             data-genre="Adventure">
            <p>STANDARD EDITION</p>
            <img src="../Assets/Game_Images/LEGEND_OF_ZELDA.jpg" alt="Zelda">
            <h3>ZELDA: BREATH OF THE WILD</h3>
            <p>£49.99</p>
            <button onclick="openProduct(
                'The Legend of Zelda: Breath of the Wild (Nintendo Switch)',
                '../Assets/Game_Images/LEGEND_OF_ZELDA.jpg',
                'Explore the vast and breathtaking world of Hyrule in this open-world adventure.',
                '£49.99',
                'Nintendo',
                '12+',
                <?php echo $zeldaId; ?>
            )">View Details</button>
        </div>

        <!-- PRODUCT 8: MARIO KART 8 -->
        <div class="product"
             data-name="Mario Kart 8 Deluxe"
             data-platform="Nintendo"
             data-genre="Racing">
            <p>STANDARD EDITION</p>
            <img src="../Assets/Game_Images/Mario_Cart_Deluxe_8.jpg" alt="Mario Kart 8 Deluxe">
            <h3>MARIO KART 8 DELUXE</h3>
            <p>£39.99</p>
            <button onclick="openProduct(
                'Mario Kart 8 Deluxe (Nintendo Switch)',
                '../Assets/Game_Images/Mario_Cart_Deluxe_8.jpg',
                'Mario Kart 8 Deluxe brings high-speed racing fun with iconic Nintendo characters and tracks.',
                '£39.99',
                'Nintendo',
                '3+',
                <?php echo $marioId; ?>
            )">View Details</button>
        </div>

    </section>
</div>

<!-- PRODUCT POPUP -->
<div id="productModal" class="product-modal" style="display:none;">
    <div id="modalContent" class="product-modal-content">

        <button id="product-close-btn" type="button" onclick="closeProduct()">Close</button>

        <h1 id="modalTitle"></h1>
        <img id="modalImage" src="" alt="Game cover" width="250">
        <p id="modalDescription"></p>
        <h2 id="modalPrice"></h2>
        <p><strong>Platform:</strong> <span id="modalPlatform"></span></p>
        <p><strong>Age Rating:</strong> <span id="modalRating"></span></p>

        <?php if ($logged_in): ?>
            <!-- ADD TO CART -->
            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="game_id" id="modalGameIdAdd">
                <input type="hidden" name="redirect" value="stay">
                <button type="submit">Add to Cart</button>
            </form>

            <!-- BUY NOW -->
            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="game_id" id="modalGameIdBuy">
                <input type="hidden" name="redirect" value="basket">
                <button type="submit">Buy Now</button>
            </form>
        <?php else: ?>
            <p>Please <a href="Login_Page.php">log in</a> to add items to your basket.</p>
        <?php endif; ?>

        <hr>

        <h3>Reviews</h3>

        <?php if ($logged_in): ?>
            <form action="submit_review.php" method="post">
                <input type="hidden" name="game_id" id="modalGameIdReview">
                <label>Rating (0–5):</label><br>
                <input type="number" name="rating" min="0" max="5" required><br><br>

                <label>Comment:</label><br>
                <textarea name="comment" rows="4" cols="40" required></textarea><br><br>

                <input type="submit" value="Submit Review">
                <input type="hidden" name="submitted" value="true">
            </form>
        <?php else: ?>
            <p>Please <a href="Login_Page.php">log in</a> to leave a review.</p>
        <?php endif; ?>

    </div>
</div>

<script>
// Open product modal
function openProduct(title, image, description, price, platform, ageRating, gameId) {
    document.getElementById("modalTitle").innerText = title;
    document.getElementById("modalImage").src = image;
    document.getElementById("modalDescription").innerText = description;
    document.getElementById("modalPrice").innerText = price;
    document.getElementById("modalPlatform").innerText = platform;
    document.getElementById("modalRating").innerText = ageRating;

    // Hidden inputs for Add / Buy / Review
    const addId    = document.getElementById("modalGameIdAdd");
    const buyId    = document.getElementById("modalGameIdBuy");
    const reviewId = document.getElementById("modalGameIdReview");

    if (addId)    addId.value    = gameId;
    if (buyId)    buyId.value    = gameId;
    if (reviewId) reviewId.value = gameId;

    // If gameId is 0, those features will safely do nothing (no FK error)
    document.getElementById("productModal").style.display = "flex";
    document.getElementById("modalContent").style.display = "flex";
}

// Close modal
function closeProduct() {
    document.getElementById("productModal").style.display = "none";
    document.getElementById("modalContent").style.display = "none";
}

// Apply filters (front-end only)
function applyFilters() {
    var searchValue   = document.getElementById("searchInput").value.toLowerCase();
    var platformValue = document.getElementById("platformSelect").value;
    var genreValue    = document.getElementById("genreSelect").value;

    var products = document.querySelectorAll(".product");

    products.forEach(function (product) {
        var name     = product.getAttribute("data-name").toLowerCase();
        var platform = product.getAttribute("data-platform");
        var genre    = product.getAttribute("data-genre");

        var visible = true;

        if (searchValue && !name.includes(searchValue)) visible = false;
        if (platformValue !== "All Platforms" && platform !== platformValue) visible = false;
        if (genreValue !== "All Genres" && !genre.toLowerCase().includes(genreValue.toLowerCase())) visible = false;

        product.style.display = visible ? "block" : "none";
    });
}
</script>

</body>
</html>
