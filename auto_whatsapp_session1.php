<?php
// =======================================================
// ✅ DATABASE CONNECTION
// =======================================================
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "veeramalla_attendance_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// =======================================================
// ✅ WHATSAPP CONFIGURATION
// =======================================================
$instance_id = "instance151177";  
$token       = "b8lsb4okb5lbzsk1"; 
$teacher_number = "+919502112287"; // Recipient number

// =======================================================
// ✅ FETCH LATECOMERS AND ABSENTEES FOR TODAY
// =======================================================
// Adjust 'session_id' as per your table if you have multiple sessions
$today = date('Y-m-d');

$sql = "SELECT regd_no, status, timestamp 
        FROM attendance_records 
        WHERE DATE(timestamp) = '$today' 
          AND (status = 'Absent' OR status = 'Late')";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $regd_no = $row['regd_no'];
        $status  = $row['status'];
        $time    = $row['timestamp'];

        $message = "📘 Regd_No: $regd_no\nStatus: $status\nTime: $time";

        // ===================================================
        // ✅ SEND WHATSAPP MESSAGE USING UltraMsg URL
        // ===================================================
        $url = "https://api.ultramsg.com/$instance_id/messages/chat";
        $data = [
            "token" => $token,
            "to"    => $teacher_number,
            "body"  => $message,
            "priority" => 10
        ];

        // Send via cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        echo "Sent message for $regd_no - $status: $response\n";
    }
} else {
    echo "No latecomers or absentees for today.";
}

// =======================================================
// ✅ CLOSE CONNECTION
// =======================================================
$conn->close();
?>
