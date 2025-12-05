<?php
$conn = new mysqli("localhost", "root", "", "veeramalla_attendance_portal");
$regd = $_GET['regd_no'];
$res = $conn->query("SELECT timestamp FROM attendance_records WHERE regd_no='$regd'");
$row = $res->fetch_assoc();
echo $row['timestamp'] ?? '';
?>
