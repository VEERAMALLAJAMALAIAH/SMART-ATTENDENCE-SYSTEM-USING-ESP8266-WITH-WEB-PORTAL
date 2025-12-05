<?php
// faculty_register.php
session_start();
include 'db_connect.php';

// ensure uploads folder
$uploadDir = __DIR__ . '/uploads/faculty_photos/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: faculty_register.html');
    exit;
}

$name = trim($_POST['name'] ?? '');
$regd_no = trim($_POST['regd_no'] ?? '');
$department = trim($_POST['department'] ?? '');
$designation = trim($_POST['designation'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($name === '' || $regd_no === '' || $password === '') {
    die('Missing required fields.');
}

// hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// handle photo upload
$photoPath = null;
if (!empty($_FILES['photo']['name'])) {
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg','jpeg','png','gif'];
    if (!in_array(strtolower($ext), $allowed)) {
        die('Invalid image type.');
    }
    $fileName = $regd_no . '_' . time() . '.' . $ext;
    $dest = $uploadDir . $fileName;
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
        die('Failed to move uploaded file.');
    }
    // store relative path for web use
    $photoPath = 'uploads/faculty_photos/' . $fileName;
}

// insert using prepared statement
$stmt = $conn->prepare("INSERT INTO faculty (regd_no, name, phone, department, email, designation, photo, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("SQL prepare failed: " . $conn->error);
}
$stmt->bind_param("ssssssss", $regd_no, $name, $phone, $department, $email, $designation, $photoPath, $hash);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    echo "<script>alert('✅ Faculty registered successfully'); window.location='faculty_login.html';</script>";
    exit;
} else {
    // handle duplicate keys etc
    $err = $stmt->error;
    $stmt->close();
    $conn->close();
    echo "<script>alert('❌ Registration failed: " . addslashes($err) . "'); window.history.back();</script>";
    exit;
}
?>
