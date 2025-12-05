<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veeramalla_attendance_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$regdno = $_GET['regdno'];

$sql = "SELECT * FROM studentregistration WHERE regd_no='$regdno'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode($data);
} else {
    echo json_encode(null);
}

$conn->close();
?>
