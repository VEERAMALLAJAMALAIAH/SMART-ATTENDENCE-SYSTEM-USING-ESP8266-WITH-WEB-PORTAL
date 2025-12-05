<?php
// events.php  — Server-Sent Events endpoint
set_time_limit(0);
ignore_user_abort(true);

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

require_once 'db_connect.php'; // must define $conn (mysqli)

// last id processed by this SSE connection (client may send last-event-id header)
$last_id = 0;
if (isset($_SERVER['HTTP_LAST_EVENT_ID'])) {
    $last_id = (int) $_SERVER['HTTP_LAST_EVENT_ID'];
}

// Helper to send an event
function send_event($data) {
    echo "data: " . json_encode($data) . "\n\n";
    @ob_flush();
    @flush();
}

// On connect, find current max id to avoid replaying old scans
$res = $conn->query("SELECT MAX(id) as mx FROM esp_scans");
$row = $res->fetch_assoc();
$last_id_local = (int)($row['mx'] ?? 0);
if ($last_id_local > $last_id) $last_id = $last_id_local;

// Loop forever (until client disconnect)
while (!connection_aborted()) {
    // fetch all scans with id > $last_id
    $stmt = $conn->prepare("SELECT id, regd_no, ts FROM esp_scans WHERE id > ? ORDER BY id ASC LIMIT 10");
    $stmt->bind_param('i', $last_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($r = $result->fetch_assoc()) {
        $last_id = (int)$r['id'];
        // optionally enrich event with student name from studentregistration
        $regd = $conn->real_escape_string($r['regd_no']);
        $sres = $conn->query("SELECT student_name FROM studentregistration WHERE regd_no='$regd' LIMIT 1");
        $student_name = null;
        if ($sres && $sres->num_rows) {
            $student_name = $sres->fetch_assoc()['student_name'];
        }
        $evt = [
            'id' => $r['id'],
            'regd_no' => $r['regd_no'],
            'student_name' => $student_name,
            'ts' => $r['ts']
        ];
        send_event($evt);
    }
    $stmt->close();
    // Wait 1 second before next poll
    sleep(1);
}
