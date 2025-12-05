<?php
// mark_scan.php
// called by RFID scanner via HTTP POST (application/x-www-form-urlencoded or JSON)
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if(!$data) parse_str($raw, $data);
$regd = $data['regdno'] ?? $data['regd'] ?? '';
$session = intval($data['session'] ?? 0);
if(!$regd || !$session){
  http_response_code(400); echo json_encode(['error'=>'Missing regdno or session']); exit;
}
$conn = new mysqli('localhost','root','','veeramalla_attendance_portal');
if($conn->connect_error){ http_response_code(500); echo json_encode(['error'=>'DB']); exit; }

// find student details
$stmt = $conn->prepare("SELECT program, year, department, section FROM studentregistration WHERE regdno=?");
$stmt->bind_param('s',$regd); $stmt->execute(); $r = $stmt->get_result()->fetch_assoc();
if(!$r){ http_response_code(404); echo json_encode(['error'=>'Student not found']); exit; }

$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');
$program = $r['program']; $year = $r['year']; $dept = $r['department']; $sec = $r['section'];

// upsert attendance: if record exists for date+session+regd, update to P; else insert
// try update first:
$upd = $conn->prepare("UPDATE attendance SET status='P', time_stamp=? WHERE regdno=? AND date=? AND session_number=?");
$upd->bind_param('sssi',$now,$regd,$today,$session);
$upd->execute();
if($upd->affected_rows == 0){
  // insert new
  $ins = $conn->prepare("INSERT INTO attendance (regdno,date,session_number,program,year,department,section,time_stamp,status) VALUES (?,?,?,?,?,?,?,?, 'P')");
  $ins->bind_param('ssisisss',$regd, $today, $session, $program, $year, $dept, $sec, $now);
  // Bind param order was off; correct:
  $ins = $conn->prepare("INSERT INTO attendance (regdno,date,session_number,program,year,department,section,time_stamp,status) VALUES (?,?,?,?,?,?,?,?,'P')");
  $ins->bind_param('sisssssi', $regd, $today, $session, $program, $year, $dept, $sec, $now);
  // Simpler safe route: do this without prepared issues (escape)
  $regd_e = $conn->real_escape_string($regd);
  $prog_e = $conn->real_escape_string($program);
  $year_e = $conn->real_escape_string($year);
  $dept_e = $conn->real_escape_string($dept);
  $sec_e = $conn->real_escape_string($sec);
  $now_e = $conn->real_escape_string($now);
  $sql = "INSERT INTO attendance (regdno,date,session_number,program,year,department,section,time_stamp,status) VALUES ('$regd_e','$today',$session,'$prog_e','$year_e','$dept_e','$sec_e','$now_e','P')";
  $conn->query($sql);
}

echo json_encode(['success'=>true,'regdno'=>$regd,'time_stamp'=>$now]);
