<?php
function renderHeader($title, $active = "", $extraHead = "")
{
    $loggedIn = isset($_SESSION["user_id"]) && ($_SESSION["role"] ?? "") === "admin";
    $cls = function ($name) use ($active) {
        return $active === $name ? ' class="active"' : '';
    };
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | <?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="style.css">
    <?= $extraHead ?>
</head>
<body>
<header class="site-header">
    <div class="container">
        <a href="index.html" class="logo">STUDENTHUB</a>
        <nav aria-label="Main Navigation">
            <ul class="nav-list">
                <li><a href="index.html">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="events.html">Events</a></li>
                <li><a href="faq.html">FAQ</a></li>
                <li><a href="contact.html">Contact</a></li>
                <?php if ($loggedIn): ?>
                    <li><a href="admin.php"<?= $cls("admin") ?>>Dashboard</a></li>
                    <li><a href="events.php"<?= $cls("events") ?>>Manage Events</a></li>
                    <li><a href="student-manager.php"<?= $cls("students") ?>>Students</a></li>
                    <li><a href="logout.php" class="login-btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="admin-login.php" class="login-btn<?= $active === "login" ? " active" : "" ?>">Admin Login</a></li>
                <?php endif; ?>
                <li><button id="darkModeBtn">Dark Mode</button></li>
            </ul>
        </nav>
    </div>
</header>
<main>
<?php
}

function renderFooter($extraScripts = "")
{
    ?>
</main>
<footer class="site-footer">
    <div class="container footer-content">
        <div>
            <h2>STUDENTHUB</h2>
            <p>A centralized student portal for campus life, events and student services.</p>
        </div>
        <nav aria-label="Footer Navigation">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="events.html">Events</a></li>
                <li><a href="faq.html">FAQ</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </nav>
    </div>
</footer>
<script src="script.js"></script>
<?= $extraScripts ?>
</body>
</html>
<?php
}
