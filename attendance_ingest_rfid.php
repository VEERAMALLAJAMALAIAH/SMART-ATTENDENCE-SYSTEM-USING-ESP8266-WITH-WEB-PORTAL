<?php
// attendance_ingest_rfid.php
include 'db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if(!$input) $input = $_POST;

$device_key = $input['device_key'] ?? '';
$tag = trim($input['tag'] ?? '');
$ts = $input['timestamp'] ?? date('Y-m-d H:i:s');

if(!$device_key || !$tag){
  echo json_encode(['error'=>'missing device_key or tag']); exit;
}

// find device id and update last_seen
$device_id = null;
$stmt = $conn->prepare("SELECT id FROM device WHERE device_key=? LIMIT 1");
$stmt->bind_param("s",$device_key);
$stmt->execute();
$res = $stmt->get_result();
if($row = $res->fetch_assoc()){
  $device_id = $row['id'];
  $u = $conn->prepare("UPDATE device SET last_seen = ? WHERE id=?");
  $u->bind_param("si", $ts, $device_id); $u->execute();
} else {
  // optional: insert device if unknown
  $ins = $conn->prepare("INSERT INTO device (device_key, device_name, last_seen) VALUES (?, ?, ?)");
  $name = "RFID-device-".$device_key;
  $ins->bind_param("sss",$device_key,$name,$ts); $ins->execute();
  $device_id = $ins->insert_id;
}

// find student by rfid_tag
$sstmt = $conn->prepare("SELECT id, regd_no, student_name FROM student WHERE rfid_tag=? LIMIT 1");
$sstmt->bind_param("s", $tag);
$sstmt->execute();
$sr = $sstmt->get_result()->fetch_assoc();

if(!$sr){
  // unknown tag - still insert a record with method RFID but without student
  $ins = $conn->prepare("INSERT INTO attendance (student_id, regd_no, student_name, method, device_id, scan_value, timestamp, matched, status) VALUES (NULL, ?, ?, 'RFID', ?, ?, ?, 0, 'A')");
  $regd_unknown = $tag; $name_unknown = 'Unknown';
  $ins->bind_param("ssiss", $regd_unknown, $name_unknown, $device_id, $tag, $ts);
  $ins->execute();
  echo json_encode(['ok'=>false,'msg'=>'tag not matched to any student']); exit;
}

// insert an RFID attendance row (provisional matched=0)
$ins = $conn->prepare("INSERT INTO attendance (student_id, regd_no, student_name, method, device_id, scan_value, timestamp, matched, status) VALUES (?, ?, ?, 'RFID', ?, ?, ?, 0, 'A')");
$ins->bind_param("ississ", $sr['id'], $sr['regd_no'], $sr['student_name'], $device_id, $tag, $ts);
$ins->execute();
$attendance_id = $ins->insert_id;

// (Matching with biometric will be attempted when biometric POSTs.)
// Return success
echo json_encode(['ok'=>true,'attendance_id'=>$attendance_id,'student_id'=>$sr['id'],'regd_no'=>$sr['regd_no']]);
