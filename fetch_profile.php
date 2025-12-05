<?php
$conn = new mysqli("localhost", "root", "", "veeramalla_attendance_portal");
$regdno = $_POST['regdno'];
$result = $conn->query("SELECT * FROM studentregistration WHERE regdno='$regdno'");
$student = $result->fetch_assoc();
echo json_encode($student);
?>
