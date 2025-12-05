<?php
// attendance_live.php
include 'db_connect.php';
header('Content-Type: application/json; charset=utf-8');

$program = $_GET['program'] ?? '';
$department = $_GET['department'] ?? '';
$year = $_GET['year'] ?? '';
$semester = $_GET['semester'] ?? '';
$section = $_GET['section'] ?? '';

if(!$program || !$department || !$year || !$semester || !$section){
  echo json_encode(['error'=>'missing parameters']); exit;
}

// 1) Get today's attendance for this class (today date)
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');

// Get attendance rows (joined with student)
$sql = "SELECT a.id, a.student_id, a.regd_no, a.student_name, a.timestamp, a.status, a.matched
        FROM attendance a
        JOIN student s ON s.regd_no = a.regd_no
        WHERE s.program = ? AND s.department = ? AND s.year = ? AND s.semester = ? AND s.section = ?
          AND a.timestamp BETWEEN ? AND ?
        ORDER BY a.timestamp ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $program, $department, $year, $semester, $section, $today_start, $today_end);
$stmt->execute();
$res = $stmt->get_result();
$attendance = [];
$seen_students = [];
while($r = $res->fetch_assoc()){
  $attendance[] = [
    'id'=>$r['id'],
    'student_id'=>$r['student_id'],
    'regd_no'=>$r['regd_no'],
    'student_name'=>$r['student_name'],
    'timestamp'=>$r['timestamp'],
    'status'=>$r['status'],
    'matched'=> (bool)$r['matched']
  ];
  $seen_students[$r['regd_no']] = true;
}

// 2) Build absentees list = students in class not in seen_students
$sq2 = "SELECT regd_no, student_name FROM student WHERE program=? AND department=? AND year=? AND semester=? AND section=?";
$stmt2 = $conn->prepare($sq2);
$stmt2->bind_param("sssss", $program, $department, $year, $semester, $section);
$stmt2->execute();
$r2 = $stmt2->get_result();
$absentees = [];
while($s = $r2->fetch_assoc()){
  if(!isset($seen_students[$s['regd_no']])){
    $absentees[] = $s;
  }
}

// 3) Device statuses: check `device` table for registered device keys
// (you may set device rows when registering devices)
$deviceInfo = ['rfid_connected'=>false, 'bio_connected'=>false];
// You can update these checks by storing last_seen and a small heartbeat from device
$dq = $conn->query("SELECT device_name, device_key, last_seen FROM device");
if($dq){
  while($dd = $dq->fetch_assoc()){
    // we'll assume device names include 'RFID' or 'BIO'
    $ls = strtotime($dd['last_seen']);
    if($ls && time() - $ls < 60*5){ // seen within last 5 minutes
      if(stripos($dd['device_name'],'rfid')!==false) $deviceInfo['rfid_connected'] = true;
      if(stripos($dd['device_name'],'bio')!==false) $deviceInfo['bio_connected'] = true;
    }
  }
}

// return JSON
echo json_encode([
  'attendance'=>$attendance,
  'absentees'=>$absentees,
  'device'=>$deviceInfo
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
