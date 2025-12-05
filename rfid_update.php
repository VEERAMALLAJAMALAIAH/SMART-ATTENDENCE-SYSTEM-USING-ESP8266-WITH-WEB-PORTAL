<?php
header("Content-Type: application/json");
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veeramalla_attendance_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "DB connection failed"]));
}

date_default_timezone_set("Asia/Kolkata");
$faculty_name = "Dr.P.VENKAT RAO";
$faculty_regd_no = "T657";

if (!isset($_GET['regdno'])) {
    echo json_encode(["success" => false, "message" => "Missing regdno"]);
    exit;
}

$regd_no = $_GET['regdno'];
$timestamp = date("Y-m-d H:i:s");

// Check student
$studentQuery = $conn->query("SELECT * FROM studentregistration WHERE regd_no='$regd_no'");
if ($studentQuery->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Student not found"]);
    exit;
}

$stu = $studentQuery->fetch_assoc();
$program = $stu['program'];
$year = $stu['year'];
$department = $stu['department'];
$section = $stu['section'];

// Get course info
$courseQuery = $conn->query("SELECT course_code FROM course_registration WHERE faculty_regd_no='$faculty_regd_no' LIMIT 1");
$course_code = ($courseQuery->num_rows > 0) ? $courseQuery->fetch_assoc()['course_code'] : 'N/A';

// ✅ Mark attendance only for scanned RFID
$sql = "INSERT INTO attendance_records 
        (regd_no, program, year, department, section, course_code, faculty_name, timestamp, status)
        VALUES ('$regd_no', '$program', '$year', '$department', '$section', '$course_code', '$faculty_name', '$timestamp', 'P')
        ON DUPLICATE KEY UPDATE status='P', timestamp='$timestamp'";
$conn->query($sql);

// Also push event for live refresh
$conn->query("INSERT INTO esp_scans (regd_no, ts) VALUES ('$regd_no', '$timestamp')");

echo json_encode(["success" => true, "regd_no" => $regd_no, "timestamp" => $timestamp]);
?>
