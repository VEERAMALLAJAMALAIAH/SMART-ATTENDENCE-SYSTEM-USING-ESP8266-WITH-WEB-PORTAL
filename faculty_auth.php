<?php
// faculty_auth.php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: faculty_login.html');
    exit;
}

$regd_no = trim($_POST['regd_no'] ?? '');
$password = $_POST['password'] ?? '';

if ($regd_no === '' || $password === '') {
    echo "<script>alert('Enter credentials'); window.location='faculty_login.html';</script>";
    exit;
}

$stmt = $conn->prepare("SELECT id, regd_no, name, photo, password FROM faculty WHERE regd_no = ? LIMIT 1");
if (!$stmt) die("SQL error: " . $conn->error);
$stmt->bind_param("s", $regd_no);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $stmt->close();
    echo "<script>alert('User not found'); window.location='faculty_login.html';</script>";
    exit;
}

$row = $res->fetch_assoc();
$hash = $row['password'];

if (password_verify($password, $hash)) {
    // success: set session and redirect to dashboard
    $_SESSION['faculty_regd_no'] = $row['regd_no'];
    $_SESSION['faculty_name'] = $row['name'];
    $_SESSION['faculty_photo'] = $row['photo'] ?? 'uploads/faculty_photos/default_user.png';
    header('Location: faculty_dashboard.php');
    exit;
} else {
    echo "<script>alert('Incorrect password'); window.location='faculty_login.html';</script>";
    exit;
}
?>
