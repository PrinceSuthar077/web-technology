<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: admin-login.php");
    exit();
}

$role = strtolower(trim($_SESSION["role"] ?? ""));
if (!in_array($role, ["admin", "student", "faculty"], true)) {
    http_response_code(403);
    exit("Access denied. Invalid role.");
}

if (isset($_SESSION["last_activity"]) && time() - (int)$_SESSION["last_activity"] > 300) {
    $_SESSION = [];
    session_destroy();
    header("Location: admin-login.php?timeout=1");
    exit();
}

$_SESSION["last_activity"] = time();

require_once "db.php";
require_once "layout.php";

function tableExists($conn, $tableName)
{
    $allowed = ["users", "students", "events"];

    if (!in_array($tableName, $allowed, true)) {
        return false;
    }

    $sql = "SELECT COUNT(*) AS total
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
              AND table_name = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "s", $tableName);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $total);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return (int)$total > 0;
}

function columnExists($conn, $tableName, $columnName)
{
    $allowedTables = ["users", "students", "events"];
    $allowedColumns = ["id", "ID", "username", "email", "role", "created_at", "name"];

    if (
        !in_array($tableName, $allowedTables, true) ||
        !in_array($columnName, $allowedColumns, true) ||
        !tableExists($conn, $tableName)
    ) {
        return false;
    }

    $sql = "SELECT COUNT(*) AS total
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = ?
              AND column_name = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ss", $tableName, $columnName);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $total);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return (int)$total > 0;
}

function getCount($conn, $tableName)
{
    if (!tableExists($conn, $tableName)) {
        return 0;
    }

    $allowed = ["users", "students", "events"];

    if (!in_array($tableName, $allowed, true)) {
        return 0;
    }

    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$tableName`");

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);

    return (int)($row["total"] ?? 0);
}

$totalUsers = 0;
$totalStudents = 0;
$totalEvents = 0;
$latestUsers = [];
$adminEmail = "-";
$dashboardMessage = "";

if ($role === "admin") {
    $totalUsers = getCount($conn, "users");
    $totalStudents = getCount($conn, "students");
    $totalEvents = getCount($conn, "events");

    if (tableExists($conn, "users")) {
        $userIdColumn = columnExists($conn, "users", "id") ? "id" : "";
        $usernameColumn = columnExists($conn, "users", "username") ? "username" : "";
        $emailColumn = columnExists($conn, "users", "email") ? "email" : "";
        $roleColumn = columnExists($conn, "users", "role") ? "role" : "";
        $createdColumn = columnExists($conn, "users", "created_at") ? "created_at" : "";

        if ($emailColumn !== "" && $userIdColumn !== "") {
            $stmt = mysqli_prepare($conn, "SELECT email FROM users WHERE id = ? LIMIT 1");

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
                mysqli_stmt_execute($stmt);

                $emailResult = mysqli_stmt_get_result($stmt);
                $emailRow = mysqli_fetch_assoc($emailResult);

                if ($emailRow) {
                    $adminEmail = $emailRow["email"] ?? "-";
                }

                mysqli_stmt_close($stmt);
            }
        }

        if ($userIdColumn !== "" && $usernameColumn !== "") {
            $select = "`$userIdColumn` AS id, `$usernameColumn` AS username";
            $select .= $emailColumn !== "" ? ", `$emailColumn` AS email" : ", '' AS email";
            $select .= $roleColumn !== "" ? ", `$roleColumn` AS role" : ", '' AS role";
            $select .= $createdColumn !== "" ? ", `$createdColumn` AS registered_at" : ", NULL AS registered_at";

            $order = $createdColumn !== "" ? "`$createdColumn` DESC" : "`$userIdColumn` DESC";

            $result = mysqli_query(
                $conn,
                "SELECT $select FROM users ORDER BY $order LIMIT 5"
            );

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $latestUsers[] = $row;
                }
            }
        }
    }

    $auditExists = false;
    $auditCheck = mysqli_query($conn, "SHOW TABLES LIKE 'audit_logs'");

    if ($auditCheck) {
        $auditExists = mysqli_num_rows($auditCheck) > 0;
    }

    if ($auditExists) {
        $action = "Viewed admin dashboard";
        $details = "Admin opened the dashboard.";
        $ipAddress = $_SERVER["REMOTE_ADDR"] ?? "";

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO audit_logs (user_id, username, action, details, ip_address)
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            $userId = (int)$_SESSION["user_id"];
            $loggedUsername = (string)($_SESSION["username"] ?? "Admin");

            mysqli_stmt_bind_param(
                $stmt,
                "issss",
                $userId,
                $loggedUsername,
                $action,
                $details,
                $ipAddress
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    } else {
        $dashboardMessage = "Audit logging is not enabled yet. Run the PR 14 SQL setup to create audit_logs.";
    }
}

renderHeader("Dashboard", "admin");
?>

<section class="page-header" aria-labelledby="page-title">
    <div class="container">
        <p class="section-label">STUDENTHUB ADMINISTRATION</p>

        <h1 id="page-title">
            <?= htmlspecialchars(ucfirst($role), ENT_QUOTES, "UTF-8") ?> Dashboard
        </h1>

        <p>
            Welcome,
            <?= htmlspecialchars($_SESSION["username"] ?? "User", ENT_QUOTES, "UTF-8") ?>.
        </p>

        <p>
            Your role:
            <strong><?= htmlspecialchars(ucfirst($role), ENT_QUOTES, "UTF-8") ?></strong>
        </p>

        <?php if (isset($_GET["timeout"])): ?>
            <p class="alert error">
                Your session expired. Please log in again.
            </p>
        <?php endif; ?>

        <?php if ($dashboardMessage !== ""): ?>
            <p class="alert">
                <?= htmlspecialchars($dashboardMessage, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>
    </div>
</section>

<?php if ($role === "admin"): ?>

<section aria-labelledby="stats-title">
    <div class="container">
        <div class="section-heading">
            <p class="section-label">OVERVIEW</p>
            <h2 id="stats-title">Dashboard Statistics</h2>
        </div>

        <div class="feature-grid">
            <article class="feature-card">
                <h3><?= $totalUsers ?></h3>
                <p>Total registered users</p>
            </article>

            <article class="feature-card">
                <h3><?= $totalStudents ?></h3>
                <p>Total students</p>
            </article>

            <article class="feature-card">
                <h3><?= $totalEvents ?></h3>
                <p>Total events</p>
            </article>
        </div>
    </div>
</section>

<section aria-labelledby="admin-actions-title">
    <div class="container">
        <div class="section-heading">
            <h2 id="admin-actions-title">Admin Actions</h2>
        </div>

        <div class="feature-grid">
            <article class="feature-card">
                <h3>Manage Events</h3>
                <p>Add, update, delete and view events.</p>
                <a href="events.php" class="btn primary-btn">Open Event Manager</a>
            </article>

            <article class="feature-card">
                <h3>Manage Students</h3>
                <p>Add, edit, delete, search and filter students.</p>
                <a href="student-manager.php" class="btn primary-btn">Open Student Manager</a>
            </article>
        </div>
    </div>
</section>

<section aria-labelledby="admin-details-title">
    <div class="container">
        <div class="section-heading">
            <h2 id="admin-details-title">Current Admin Details</h2>
        </div>

        <div class="table-wrap">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($_SESSION["username"] ?? "Admin", ENT_QUOTES, "UTF-8") ?></td>
                        <td><?= htmlspecialchars($adminEmail, ENT_QUOTES, "UTF-8") ?></td>
                        <td>Admin</td>
                        <td>Active</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section aria-labelledby="latest-users-title">
    <div class="container">
        <div class="section-heading">
            <h2 id="latest-users-title">Latest Registrations</h2>
        </div>

        <?php if (count($latestUsers) > 0): ?>
            <div class="table-wrap">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered At</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($latestUsers as $user): ?>
                            <tr>
                                <td><?= (int)$user["id"] ?></td>
                                <td><?= htmlspecialchars($user["username"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($user["email"] ?? "-", ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($user["role"] ?? "-", ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($user["registered_at"] ?? "-", ENT_QUOTES, "UTF-8") ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No user registrations found, or the users table does not have the expected id and username columns.</p>
        <?php endif; ?>
    </div>
</section>

<?php elseif ($role === "student"): ?>

<section>
    <div class="container">
        <h2>Student Services</h2>
        <p>Use StudentHub to view events and manage your profile.</p>
        <a class="btn primary-btn" href="events.html">View Events</a>
    </div>
</section>

<?php else: ?>

<section>
    <div class="container">
        <h2>Faculty Services</h2>
        <p>Use StudentHub to view college events and discussions.</p>
        <a class="btn primary-btn" href="events.html">View Events</a>
    </div>
</section>

<?php endif; ?>

<?php
renderFooter();
mysqli_close($conn);
?>