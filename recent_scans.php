<?php
// recent_scans.php?since=unix_ts
header('Content-Type: application/json');
$since = intval($_GET['since'] ?? 0);
$conn = new mysqli('localhost','root','','veeramalla_attendance_portal');
$today = date('Y-m-d');
$q = "SELECT regdno, UNIX_TIMESTAMP(time_stamp) as ts, time_stamp FROM attendance WHERE date='$today' AND UNIX_TIMESTAMP(time_stamp) > $since ORDER BY time_stamp ASC";
$res = $conn->query($q);
$scans = [];
while($r = $res->fetch_assoc()){
  $scans[] = ['regdno'=>$r['regdno'],'ts_unix'=>intval($r['ts']),'time_stamp'=>$r['time_stamp']];
}
echo json_encode(['scans'=>$scans]);
