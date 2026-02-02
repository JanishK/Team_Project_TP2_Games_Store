console.log("App.js loaded");
// --- user dropdown ---
const userBtn = document.getElementById("cbUserBtn");
const userMenu = document.getElementById("cbUserMenu");

if (userBtn && userMenu) {
    console.log("User dropdown script running");
    userBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    userMenu.classList.toggle("hidden");
    userBtn.setAttribute("aria-expanded", String(!userMenu.classList.contains("hidden")));
    });

    document.addEventListener("click", () => {
    userMenu.classList.add("hidden");
    userBtn.setAttribute("aria-expanded", "false");
    });
}

// --- modal ---
function openProduct(title, image, description, price, platform, ageRating, gameId) {
    document.getElementById("modalTitle").innerText = title;
    document.getElementById("modalImage").src = image;
    document.getElementById("modalDescription").innerText = description;
    document.getElementById("modalPrice").innerText = price;
    document.getElementById("modalPlatform").innerText = platform;
    document.getElementById("modalRating").innerText = ageRating;

    const addId = document.getElementById("modalGameIdAdd");
    const buyId = document.getElementById("modalGameIdBuy");
    const reviewId = document.getElementById("modalGameIdReview");

    if (addId) addId.value = gameId;
    if (buyId) buyId.value = gameId;
    if (reviewId) reviewId.value = gameId;

    document.getElementById("productModal").style.display = "flex";
    document.getElementById("modalContent").style.display = "flex";
}

function closeProduct() {
    document.getElementById("productModal").style.display = "none";
    document.getElementById("modalContent").style.display = "none";
}

// close modal if you click background
const productModal = document.getElementById("productModal");

if (productModal) {
    productModal.addEventListener("click", (e) => {
        if (e.target.id === "productModal") closeProduct();
    });
}


// --- filters ---
function applyFilters() {
    const searchValue = document.getElementById("searchInput").value.toLowerCase().trim();
    const platformValue = document.getElementById("platformSelect").value;
    const ageValue = document.getElementById("ageSelect").value;

    const products = document.querySelectorAll(".product");

    products.forEach((product) => {
    const name = (product.getAttribute("data-name") || "").toLowerCase();
    const platform = product.getAttribute("data-platform") || "";
    const age = product.getAttribute("data-age") || "";

    let visible = true;

    if (searchValue && !name.includes(searchValue)) visible = false;
    if (platformValue !== "All Platforms" && platform !== platformValue) visible = false;
    if (ageValue !== "All Ratings" && age !== ageValue) visible = false;

    product.style.display = visible ? "block" : "none";
    });
}

function resetFilters() {
    document.getElementById("searchInput").value = "";
    document.getElementById("platformSelect").value = "All Platforms";
    document.getElementById("ageSelect").value = "All Ratings";
    applyFilters();
}

// --- live theme preview ---
const themeSelect = document.getElementById("theme");

if (themeSelect) {
  themeSelect.addEventListener("change", () => {
    if (themeSelect.value === "light") {
      document.body.classList.add("light-mode");
    } else {
      document.body.classList.remove("light-mode");
    }
  });
}
