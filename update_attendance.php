<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veeramalla_attendance_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("❌ Database connection failed: " . $conn->connect_error);

if (isset($_GET['regdno'])) {
    $regdno = $_GET['regdno'];
    date_default_timezone_set("Asia/Kolkata");
    $timestamp = date("Y-m-d H:i:s");

    // Verify student exists
    $check = $conn->query("SELECT * FROM studentregistration WHERE regd_no='$regdno'");
    if ($check->num_rows > 0) {
        // Insert or update attendance
        $conn->query("INSERT INTO attendance_records (regd_no, timestamp, status)
                      VALUES ('$regdno', '$timestamp', 'P')");
        echo "✅ Attendance marked successfully for $regdno at $timestamp";
    } else {
        echo "⚠️ Student not found in studentregistration table";
    }
} else {
    echo "❌ Invalid request. Missing ?regdno parameter.";
}
$conn->close();
?>
