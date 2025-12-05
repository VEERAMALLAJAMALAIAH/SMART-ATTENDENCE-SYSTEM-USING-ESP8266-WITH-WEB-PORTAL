<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veeramalla_attendance_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM attendance_board ORDER BY regd_no ASC");
$sn = 1;
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$sn}</td>";
    echo "<td>{$row['regd_no']}</td>";
    echo "<td>{$row['student_name']}</td>";
    echo "<td style='color:" . ($row['status'] == 'P' ? 'green' : 'red') . "; font-weight:bold;'>{$row['status']}</td>";
    echo "<td>{$row['timestamp']}</td>";
    echo "</tr>";
    $sn++;
}
$conn->close();
?>
