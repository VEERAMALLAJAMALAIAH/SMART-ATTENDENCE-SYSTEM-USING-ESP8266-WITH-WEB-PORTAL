<?php
/* ===============================================================
   AUTO WHATSAPP SENDER FOR SESSION–1  (UltraMsg)
   =============================================================== */

date_default_timezone_set("Asia/Kolkata");
$today = date("Y-m-d");
$current_time = date("H:i:s");

/* ================== ULTRAMSG CONFIG =================== */
$instance_id = "instance151177"; 
$token       = "b8lsb4obk5lbzsk1";

$hod_number  = "+91XXXXXXXXXX";
$classteacher_number = "+91XXXXXXXXXX";

/* =============== DB CONNECTION ===================== */
$conn = new mysqli("localhost","root","","veeramalla_attendance_portal");
if ($conn->connect_error) {
    die("DB Failed");
}

/* ===============================================================
   ONLY RUN FOR SESSION 1
   =============================================================== */
$session = 1;

/* ===============================================================
   FETCH ALL LATE COMERS (status = 'P' AND late = 1)
   =============================================================== */
$late_sql = "
    SELECT regd_no, timestamp, student_name
    FROM attendance_records
    WHERE DATE(timestamp) = '$today'
    AND session = '1'
    AND status = 'P'
    AND late = 1
";

$late_result = $conn->query($late_sql);
$late_list = [];

while($row = $late_result->fetch_assoc()) {
    $late_list[] = [
        "regd_no" => $row["regd_no"],
        "name" => $row["student_name"],
        "time" => date("h:i:s A", strtotime($row["timestamp"]))
    ];
}

/* ===============================================================
   FETCH ABSENTEES (status = 'A')
   =============================================================== */
$abs_sql = "
    SELECT regd_no, student_name
    FROM attendance_records
    WHERE DATE(timestamp) = '$today'
    AND session = '1'
    AND status = 'A'
";
$abs_result = $conn->query($abs_sql);
$abs_list = [];

while($row = $abs_result->fetch_assoc()) {
    $abs_list[] = $row["regd_no"] . " (" . $row["student_name"] . ")";
}


/* ===============================================================
   SEND WHATSAPP USING UltraMsg
   =============================================================== */
function sendWhatsApp($msg, $to, $instance_id, $token){
    $url = "https://api.ultramsg.com/$instance_id/messages/chat";
    $data = [
        "token" => $token,
        "to"    => $to,
        "body"  => $msg
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}


/* ========== 1. FORMAT LATE COMERS MESSAGE =========== */
if (!empty($late_list)) {

    $msg = "📌 *Late Comers – Session 1*\n".
           "📅 Date: ".date("d-m-Y")."\n\n";

    foreach ($late_list as $s) {
        $msg .= "🧑 *".$s["name"]."* (".$s["regd_no"].")\n";
        $msg .= "⏱ Late At: ".$s["time"]."\n\n";
    }

    $msg .= "Regards,\nAttendance System";

    // SEND MESSAGE
    sendWhatsApp($msg, $hod_number, $instance_id, $token);
    sleep(3);
    sendWhatsApp($msg, $classteacher_number, $instance_id, $token);
}


/* ========== 2. FORMAT ABSENTEES MESSAGE =========== */
if (!empty($abs_list)) {

    $msg2  = "📌 *Absentees – Session 1*\n";
    $msg2 .= "📅 Date: ".date("d-m-Y")."\n\n";
    
    foreach($abs_list as $a){
        $msg2 .= "❌ $a\n";
    }

    $msg2 .= "\nSent Automatically\nAttendance System";

    sendWhatsApp($msg2, $hod_number, $instance_id, $token);
    sleep(3);
    sendWhatsApp($msg2, $classteacher_number, $instance_id, $token);
}

echo "DONE";
?>
