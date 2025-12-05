<?php
header('Content-Type: application/json');
include(__DIR__ . '/db_connection.php');

$sql = "SELECT regdno, name, markattendance, timestamp 
        FROM attendance_records 
        ORDER BY timestamp DESC";

$result = $conn->query($sql);
$records = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

echo json_encode($records);
?>
