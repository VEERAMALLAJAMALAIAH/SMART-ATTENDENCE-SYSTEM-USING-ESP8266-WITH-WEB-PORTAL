<?php
$conn = new mysqli("localhost", "root", "", "veeramalla_attendance_portal");
if ($conn->connect_error) die("DB Failed: " . $conn->connect_error);

$regd_no = $_POST['regd_no']; // Sent by biometric device when matched
$conn->query("UPDATE attendance_records 
              SET status='P' 
              WHERE regd_no='$regd_no' AND date=CURDATE()");

echo "Marked Present";
$conn->close();
?>
