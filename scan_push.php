<?php
// scan_push.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veeramalla_attendance_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => $conn->connect_error]));
}

$regdno = $_GET['regdno'] ?? '';

if ($regdno === '') {
    echo json_encode(["success" => false, "error" => "Missing regdno"]);
    exit;
}

// Update the status to "P" (present)
$sql = "UPDATE attendance_board
        SET status='P', timestamp=NOW()
        WHERE regd_no='$regdno'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["success" => true, "regd_no" => $regdno, "timestamp" => date("Y-m-d H:i:s")]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}

$conn->close();
?>
