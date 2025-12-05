<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veeramalla_attendance_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ DB Connection failed: " . $conn->connect_error);
}

if (isset($_GET['regdno'])) {
    $regd_no = $_GET['regdno'];
    date_default_timezone_set("Asia/Kolkata");
    $timestamp = date("Y-m-d H:i:s");

    // Check student exists
    $check = $conn->query("SELECT * FROM studentregistration WHERE regd_no='$regd_no'");
    if ($check->num_rows > 0) {
        // Find course info (assuming 1 course per faculty)
        $courseQuery = $conn->query("SELECT course_code FROM course_registration WHERE faculty_regd_no='T657' LIMIT 1");
        $course = $courseQuery->fetch_assoc();
        $course_code = $course['course_code'] ?? 'N/A';

        // Insert attendance record
        $sql = "INSERT INTO attendance_records (regd_no, course_code, timestamp, status)
                VALUES ('$regd_no', '$course_code', '$timestamp', 'P')";
        if ($conn->query($sql)) {
            echo "✅ Attendance marked for $regd_no at $timestamp";
        } else {
            echo "⚠️ Error: " . $conn->error;
        }
    } else {
        echo "❌ Unknown student: $regd_no";
    }
} else {
    echo "⚠️ No regdno received.";
}
?>
