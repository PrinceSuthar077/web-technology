document.addEventListener("DOMContentLoaded", function () {

    const button = document.getElementById("darkModeBtn");

    if (localStorage.getItem("darkMode") === "on") {
        document.body.classList.add("dark-mode");
    }

    if (button) {
        button.addEventListener("click", function () {
            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {
                localStorage.setItem("darkMode", "on");
                button.textContent = "Light Mode";
            } else {
                localStorage.setItem("darkMode", "off");
                button.textContent = "Dark Mode";
            }
        });
    }
});