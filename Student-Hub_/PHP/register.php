
<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request.");
}

$full_name = trim($_POST["full_name"] ?? "");
$student_id = trim($_POST["student_id"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$course = trim($_POST["course"] ?? "");
$semester = trim($_POST["semester"] ?? "");
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";
$terms = isset($_POST["terms"]);

$full_name = htmlspecialchars($full_name);
$student_id = htmlspecialchars($student_id);
$email = htmlspecialchars($email);
$phone = htmlspecialchars($phone);
$gender = htmlspecialchars($gender);
$course = htmlspecialchars($course);
$semester = htmlspecialchars($semester);

$errors = [];

if (!preg_match("/^[A-Za-z ]{3,50}$/", $full_name)) {
    $errors[] = "Invalid full name.";
}

if (!preg_match("/^[A-Za-z0-9-]{4,20}$/", $student_id)) {
    $errors[] = "Invalid student ID.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}

if (!preg_match("/^[6-9][0-9]{9}$/", $phone)) {
    $errors[] = "Invalid mobile number.";
}

if (!in_array($gender, ["male", "female", "other"])) {
    $errors[] = "Please select a valid gender.";
}

if ($course === "") {
    $errors[] = "Please select a course.";
}

if (!in_array($semester, ["1", "2", "3", "4", "5", "6", "7", "8"])) {
    $errors[] = "Please select a valid semester.";
}

if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/", $password)) {
    $errors[] = "Password does not meet the requirements.";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

if (!$terms) {
    $errors[] = "You must accept the terms.";
}

if (count($errors) > 0) {
    echo "<h2>Registration Failed</h2>";

    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }

    echo '<p><a href="../HTML/register.html">Go Back</a></p>';
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$file = "../data/registrations.csv";
$handle = fopen($file, "a");

if (!$handle) {
    exit("Unable to save registration.");
}

if (filesize($file) === 0) {
    fputcsv($handle, [
        "Full Name",
        "Student ID",
        "Email",
        "Phone",
        "Gender",
        "Course",
        "Semester",
        "Password Hash"
    ]);
}

fputcsv($handle, [
    $full_name,
    $student_id,
    $email,
    $phone,
    $gender,
    $course,
    $semester,
    $password_hash
]);

fclose($handle);

echo "<h2>Registration Successful!</h2>";
echo "<p>Welcome, " . htmlspecialchars($full_name) . ".</p>";
echo '<p>Your registration has been saved successfully.</p>';
echo '<p><a href="../HTML/register.html">Back to Registration</a></p>';

?>

