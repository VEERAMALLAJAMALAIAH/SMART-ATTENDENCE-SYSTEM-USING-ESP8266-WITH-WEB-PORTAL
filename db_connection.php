<?php
// db_connection.php — shared database connection

$host = "localhost";
$user = "root";       // Default XAMPP MySQL username
$pass = "";           // Leave empty (unless you set a password)
$db   = "veeramalla_attendance_portal";  // Your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
