
<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Please submit the registration form.");
}

$fullName = trim($_POST["fullName"] ?? "");
$admissionNo = trim($_POST["admissionNo"] ?? "");
$course = trim($_POST["course"] ?? "");
$phone = trim($_POST["phone"] ?? "");

$errors = [];

if (!preg_match("/^[a-zA-Z ]{2,100}$/", $fullName)) {
    $errors[] = "Enter a valid full name.";
}

if (!preg_match("/^[a-zA-Z0-9\/-]{1,30}$/", $admissionNo)) {
    $errors[] = "Enter a valid admission number.";
}

$allowedCourses = [
    "ICT",
    "Electrical Engineering",
    "Business Management",
    "Secretarial Studies"
];

if (!in_array($course, $allowedCourses, true)) {
    $errors[] = "Select a valid course.";
}

if (!preg_match("/^(?:\+254|0)\d{9}$/", $phone)) {
    $errors[] = "Enter a valid Kenyan phone number.";
}

if (!empty($errors)) {
    echo "<h2>Registration Failed</h2>";

    foreach ($errors as $error) {
        echo "<p>" .
            htmlspecialchars($error, ENT_QUOTES, "UTF-8") .
            "</p>";
    }

    echo '<a href="index.html">Go back</a>';
    exit;
}

$sql = "INSERT INTO students
        (full_name, admission_number, course, phone_number)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssss",
    $fullName,
    $admissionNo,
    $course,
    $phone
);

if ($stmt->execute()) {
    function safe($value) {
        return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Registration Successful</title>
    </head>
    <body>
        <h2>Student Registered Successfully!</h2>

        <p><strong>Full Name:</strong>
            <?= safe($fullName) ?></p>

        <p><strong>Admission Number:</strong>
            <?= safe($admissionNo) ?></p>

        <p><strong>Course:</strong>
            <?= safe($course) ?></p>

        <p><strong>Phone Number:</strong>
            <?= safe($phone) ?></p>

        <p>Your details have been saved to the database.</p>

        <a href="index.html">Register Another Student</a>
    </body>
    </html>
    <?php
} else {
    if ($stmt->errno === 1062) {
        echo "Registration failed: this admission number already exists.";
    } else {
        http_response_code(500);
        echo "Registration failed due to a database error.";
    }
}

$stmt->close();
$conn->close();
?>
