<?php
// submit_attendance.php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if(!$data) { echo json_encode(['error'=>'No payload']); exit; }
$meta = $data['meta'] ?? null;
$rows = $data['rows'] ?? [];
if(!$meta || !$rows) { echo json_encode(['error'=>'Missing data']); exit; }

$program = $meta['program']; $year = $meta['year']; $dept = $meta['department']; $section = $meta['section']; $session = intval($meta['session']);
$today = date('Y-m-d');

$conn = new mysqli('localhost','root','','veeramalla_attendance_portal');
if($conn->connect_error){ echo json_encode(['error'=>'DB']); exit; }

// Save each row (upsert)
foreach($rows as $r){
  $regd = $conn->real_escape_string($r['regd']);
  $status = ($r['status'] === 'P') ? 'P' : 'A';
  $ts = $r['time_stamp'] ? $conn->real_escape_string($r['time_stamp']) : date('Y-m-d H:i:s');
  // update if exists
  $q = "SELECT id FROM attendance WHERE regdno='$regd' AND date='$today' AND session_number=$session";
  $res = $conn->query($q);
  if($res->num_rows){
    $conn->query("UPDATE attendance SET status='$status', time_stamp='$ts' WHERE regdno='$regd' AND date='$today' AND session_number=$session");
  } else {
    $conn->query("INSERT INTO attendance (regdno,date,session_number,program,year,department,section,time_stamp,status) VALUES ('$regd','$today',$session,'$program','$year','$dept','$section','$ts','$status')");
  }
}

// build absentee list
$abs = [];
$res2 = $conn->query("SELECT a.regdno, s.name, s.phone FROM attendance a LEFT JOIN studentregistration s ON a.regdno=s.regdno WHERE a.date='$today' AND a.session_number=$session AND a.status='A'");
while($r = $res2->fetch_assoc()) $abs[] = $r;
echo json_encode(['success'=>true,'absentees'=>$abs]);
