<?php
// save_scan.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php'; // must define $conn (mysqli) or $pdo; below uses mysqli ($conn)

$raw = file_get_contents('php://input');
$json = json_decode($raw, true);

// Accept either JSON body or GET param (for quick testing)
$regd = $json['regdno'] ?? ($_GET['regdno'] ?? null);
$ts   = $json['ts'] ?? date('Y-m-d H:i:s');

if (!$regd) {
    echo json_encode(['success'=>false, 'message'=>'Missing regdno']);
    exit;
}

$regd = $conn->real_escape_string($regd);
$ts_dt = date('Y-m-d H:i:s', strtotime($ts));

// 1) insert into esp_scans (for events streaming)
$stmt = $conn->prepare("INSERT INTO esp_scans (regd_no, ts) VALUES (?, ?)");
$stmt->bind_param('ss', $regd, $ts_dt);
$stmt->execute();
$scan_id = $stmt->insert_id;
$stmt->close();

// 2) fetch student info from studentregistration
$student = null;
$sq = "SELECT regd_no, student_name, program, year, department, section, semester
       FROM studentregistration WHERE regd_no = ?";
if ($r = $conn->prepare($sq)) {
    $r->bind_param('s', $regd);
    $r->execute();
    $res = $r->get_result();
    if ($res && $res->num_rows) $student = $res->fetch_assoc();
    $r->close();
}

// 3) insert or update attendance_records: mark P with timestamp
if ($student) {
    // you may want to adjust unique key for per-day uniqueness
    $ins = $conn->prepare("INSERT INTO attendance_records 
        (regd_no, student_name, program, year, department, section, semester, timestamp, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'P')
        ON DUPLICATE KEY UPDATE status='P', timestamp=VALUES(timestamp)");
    $ins->bind_param(
        'ssssssss',
        $student['regd_no'],
        $student['student_name'],
        $student['program'],
        $student['year'],
        $student['department'],
        $student['section'],
        $student['semester'],
        $ts_dt
    );
    $ins->execute();
    $ins->close();
}

echo json_encode([
    'success' => true,
    'scan_id' => $scan_id,
    'regd_no' => $regd,
    'timestamp' => $ts_dt
]);
