<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

function respond($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit();
}

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    respond(["success" => false, "message" => "Please log in as an admin."], 401);
}

try {
    require_once "db.php";
} catch (Throwable $e) {
    respond(["success" => false, "message" => "Database connection failed."], 500);
}

$courses = ["BCA", "BSc IT", "B.Tech AIML", "B.Tech IT", "MCA"];

$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    $input = [];
}

$action = $_GET["action"] ?? ($input["action"] ?? "list");
$method = $_SERVER["REQUEST_METHOD"];

try {
    if ($action === "list") {
        if ($method !== "GET") {
            respond(["success" => false, "message" => "Use GET for listing students."], 405);
        }

        $search = trim($_GET["search"] ?? "");
        $courseFilter = trim($_GET["course"] ?? "");

        $sql = "SELECT ID AS id, name, email, course FROM students WHERE 1=1";
        $types = "";
        $params = [];

        if ($search !== "") {
            $sql .= " AND (name LIKE ? OR email LIKE ?)";
            $like = "%" . $search . "%";
            $types .= "ss";
            $params[] = $like;
            $params[] = $like;
        }

        if ($courseFilter !== "") {
            $sql .= " AND course = ?";
            $types .= "s";
            $params[] = $courseFilter;
        }

        $sql .= " ORDER BY ID DESC";

        $stmt = mysqli_prepare($conn, $sql);
        if ($types !== "") {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $students = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
        }
        mysqli_stmt_close($stmt);

        respond(["success" => true, "students" => $students, "courses" => $courses]);
    }

    if (!in_array($action, ["add", "update", "delete"], true)) {
        respond(["success" => false, "message" => "Invalid action."], 400);
    }

    if ($method !== "POST") {
        respond(["success" => false, "message" => "Use POST for this action."], 405);
    }

    if ($action === "delete") {
        $id = (int)($input["id"] ?? 0);

        if ($id <= 0) {
            respond(["success" => false, "message" => "Invalid student ID."], 400);
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE ID = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $deleted = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($deleted > 0) {
            respond(["success" => true, "message" => "Student deleted successfully."]);
        }

        respond(["success" => false, "message" => "Student not found."], 404);
    }

    $name = trim($input["name"] ?? "");
    $email = trim($input["email"] ?? "");
    $course = trim($input["course"] ?? "");

    if ($name === "" || $email === "" || $course === "") {
        respond(["success" => false, "message" => "Please fill in all fields."], 400);
    }

    if (mb_strlen($name) > 100 || mb_strlen($email) > 100) {
        respond(["success" => false, "message" => "Name and email must be 100 characters or less."], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(["success" => false, "message" => "Enter a valid email address."], 400);
    }

    if (!in_array($course, $courses, true)) {
        respond(["success" => false, "message" => "Please select a valid course."], 400);
    }

    if ($action === "add") {
        $stmt = mysqli_prepare($conn, "INSERT INTO students (name, email, course) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $course);
        mysqli_stmt_execute($stmt);
        $newId = mysqli_stmt_insert_id($stmt);
        mysqli_stmt_close($stmt);

        respond(["success" => true, "message" => "Student added successfully.", "id" => $newId], 201);
    }

    $id = (int)($input["id"] ?? 0);

    if ($id <= 0) {
        respond(["success" => false, "message" => "Invalid student ID."], 400);
    }

    $stmt = mysqli_prepare($conn, "UPDATE students SET name = ?, email = ?, course = ? WHERE ID = ?");
    mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $course, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    respond(["success" => true, "message" => "Student updated successfully."]);
} catch (Throwable $e) {
    respond(["success" => false, "message" => "Database operation failed."], 500);
}
