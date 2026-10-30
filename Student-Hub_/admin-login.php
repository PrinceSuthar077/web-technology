<?php
session_start();
require_once "db.php";
require_once "layout.php";

if (isset($_SESSION["user_id"]) && strtolower($_SESSION["role"] ?? "") === "admin") {
    header("Location: admin.php");
    exit();
}

$msg = "";
if (isset($_GET["timeout"])) {
    $msg = "Your session expired. Please log in again.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = mysqli_prepare($conn, "SELECT id, password, role FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    } else {
        $user = null;
    }

    if ($user && password_verify($password, $user["password"]) && strtolower($user["role"] ?? "") === "admin") {
        session_regenerate_id(true);
        $_SESSION["user_id"] = (int)$user["id"];
        $_SESSION["role"] = "admin";
        $_SESSION["username"] = $username;
        $_SESSION["last_activity"] = time();
        header("Location: admin.php");
        exit();
    }

    $msg = "Invalid username or password, or this account is not an admin.";
}

renderHeader("Admin Login", "login");
?>
<section class="page-header" aria-labelledby="page-title">
    <div class="container">
        <p class="section-label">ADMINISTRATION</p>
        <h1 id="page-title">Admin Login</h1>
        <p>Login to manage StudentHub.</p>
    </div>
</section>
<section class="form-section" aria-labelledby="login-title">
    <div class="container form-container">
        <?php if ($msg !== ""): ?><div class="alert error"><?= htmlspecialchars($msg, ENT_QUOTES, "UTF-8") ?></div><?php endif; ?>
        <form action="" method="post" class="event-form">
            <h2 id="login-title">Login as Admin</h2>
            <div class="form-group"><label for="username">Username</label><input type="text" id="username" name="username" autocomplete="username" required></div>
            <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="password" autocomplete="current-password" required></div>
            <button type="submit" name="login" class="btn primary-btn">Login</button>
        </form>
    </div>
</section>
<?php
renderFooter();
mysqli_close($conn);
