<?php
// load_students.php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if(!$data) { echo json_encode(['error'=>'No payload']); exit; }
$prog = $data['program'] ?? '';
$year = $data['year'] ?? '';
$dept = $data['department'] ?? '';
$sec = $data['section'] ?? '';
$session = intval($data['session'] ?? 0);
if(!$prog || !$year || !$dept || !$sec || !$session) { echo json_encode(['error'=>'Missing filters']); exit; }

$conn = new mysqli('localhost','root','','veeramalla_attendance_portal');
if($conn->connect_error) { echo json_encode(['error'=>'DB error']); exit; }

// load students left join latest attendance for today+session to show P if already scanned
$today = date('Y-m-d');
$sql = "SELECT s.regdno, s.name, a.time_stamp, a.status
        FROM studentregistration s
        LEFT JOIN attendance a ON a.regdno = s.regdno AND a.date = ? AND a.session_number = ?
        WHERE s.program=? AND s.year=? AND s.department=? AND s.section=?
        ORDER BY s.name";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sisss', $today, $session, $prog, $year, $dept, $sec); // note: parameter order: date, session, program, year, department, section
// But bind_param types must match; correct binding below:
$stmt = $conn->prepare("SELECT s.regdno, s.name, a.time_stamp, a.status
        FROM studentregistration s
        LEFT JOIN attendance a ON a.regdno = s.regdno AND a.date = ? AND a.session_number = ?
        WHERE s.program=? AND s.year=? AND s.department=? AND s.section=?
        ORDER BY s.name");
$stmt->bind_param('sissss', $today, $session, $prog, $year, $dept, $sec);
$stmt->execute();
$res = $stmt->get_result();
$students = [];
while($r = $res->fetch_assoc()){
  $students[] = [
    'regdno'=>$r['regdno'],
    'name'=>$r['name'],
    'time_stamp'=>$r['time_stamp'],
    'status'=> $r['status'] ?? 'A'
  ];
}
echo json_encode(['students'=>$students]);
