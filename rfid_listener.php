<?php
$conn = new mysqli("localhost", "root", "", "veeramalla_attendance_portal");

if ($conn->connect_error) die("DB Failed: " . $conn->connect_error);

$regd_no = $_POST['regd_no'];  // Sent by RFID scanner via POST
$time_now = date("Y-m-d H:i:s");

// Update timestamp in attendance table
$conn->query("UPDATE attendance_records 
              SET timestamp='$time_now' 
              WHERE regd_no='$regd_no' AND date=CURDATE()");

echo "Timestamp Updated";
$conn->close();
?>
