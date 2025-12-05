<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veeramalla_attendance_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$regdno = $_POST['regdno'];

// Here you’ll later replace this with real biometric device data
$sql = "UPDATE studentregistration SET biometric_registered = 1 WHERE regd_no='$regdno'";

if ($conn->query($sql) === TRUE) {
    echo "✅ Biometric Registered Successfully";
} else {
    echo "❌ Error: " . $conn->error;
}

$conn->close();
?>
