function toggleTheme() {
    const toggle = document.getElementById("themeToggle");
    const body = document.getElementById("theme");

    if (!toggle || !body) return; // prevents errors on pages without the button

    // Apply saved theme on page load
    if (localStorage.getItem("theme") === "light") {
        body.classList.add("light-mode");
        toggle.textContent = "🌙";
    } else {
        toggle.textContent = "☀️";
    }

    // Toggle on click
    toggle.addEventListener("click", () => {
        body.classList.toggle("light-mode");

        if (body.classList.contains("light-mode")) {
            localStorage.setItem("theme", "light");
            toggle.textContent = "🌙";
        } else {
            localStorage.setItem("theme", "dark");
            toggle.textContent = "☀️";
        }
    });
}

function toggleMenu() {
    const menu = document.getElementById("navMenu");
    menu.classList.toggle("show");
}
