<?php
// =======================================================
// ✅ DATABASE CONNECTION
// =======================================================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veeramalla_attendance_portal";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

session_start();
$faculty_name = $_SESSION['faculty_name'] ?? 'Dr.P.VENKAT RAO';
$faculty_regd_no = $_SESSION['faculty_regd_no'] ?? 'T657';
date_default_timezone_set("Asia/Kolkata");

// =======================================================
// ✅ ESP8266 HANDLER — UPDATE JSON ONLY, NOT DATABASE
// =======================================================
if (isset($_GET['regd_no']) && !empty($_GET['regd_no'])) {
    $regdno = trim($_GET['regd_no']);
    $timestamp = date("Y-m-d H:i:s");

    if (!preg_match('/^[0-9A-Za-z]+$/', $regdno)) {
        echo json_encode(["success" => false, "message" => "Invalid regd_no"]);
        exit;
    }

    // ✅ Check if student exists
    $check = $conn->query("SELECT * FROM studentregistration WHERE regd_no='$regdno' LIMIT 1");
    if ($check->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Student not found"]);
        exit;
    }

    // ✅ Save last scanned card for live update (not DB)
    file_put_contents("esp_scan_data.json", json_encode([
        "success" => true,
        "regd_no" => $regdno,
        "timestamp" => $timestamp
    ]));

    echo json_encode(["success" => true, "message" => "RFID received", "regd_no" => $regdno, "timestamp" => $timestamp]);
    exit;
}

// =======================================================
// ✅ FILTER OPTIONS
// =======================================================
$programs = ['B.TECH', 'M.TECH', 'M.B.A'];
$years = ['I', 'II', 'III', 'IV'];
$departments = [
    'Computer Science & Engineering',
    'Electronics & Communication Engineering',
    'Electrical & Electronics Engineering',
    'Mechanical Engineering',
    'Civil Engineering',
    'Information Technology',
    'Artificial Intelligence & Machine Learning',
    'Data Science',
    'Computer Science & Business Systems'
];
$sections = ['A', 'B', 'C', 'D', 'E'];
$semesters = ['I', 'II'];
$sessions = ['1','2','3','4','5','6','7'];

// =======================================================
// ✅ LOAD STUDENTS
// =======================================================
$students = [];
if (isset($_GET['load'])) {
    $program = $_GET['program'];
    $year = $_GET['year'];
    $department = $_GET['department'];
    $section = $_GET['section'];
    $semester = $_GET['semester'];
    $session = $_GET['session'];

    $res = $conn->query("
        SELECT DISTINCT regd_no, student_name 
        FROM studentregistration
        WHERE program='$program'
          AND year='$year'
          AND department='$department'
          AND section='$section'
          AND semester='$semester'
        ORDER BY regd_no ASC
    ");
    while ($r = $res->fetch_assoc()) {
        $students[] = $r;
    }

    file_put_contents("esp_scan_data.json", json_encode(["success" => false]));
}

// =======================================================
// ✅ SUBMIT ATTENDANCE (Manual or Auto)
// =======================================================
if (isset($_POST['submit_attendance'])) {
    $timestamp = date("Y-m-d H:i:s");
    $session = $_POST['session'] ?? '1';
    $course = $conn->query("SELECT course_code FROM course_registration WHERE faculty_regd_no='$faculty_regd_no' LIMIT 1");
    $course_code = ($course->num_rows > 0) ? $course->fetch_assoc()['course_code'] : 'N/A';

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'status_') === 0) {
            $regdno = str_replace('status_', '', $key);
            $status = $value;
            $student = $conn->query("SELECT * FROM studentregistration WHERE regd_no='$regdno' LIMIT 1")->fetch_assoc();

            $conn->query("
                INSERT INTO attendance_records 
                (regd_no, program, year, department, section, course_code, faculty_name, timestamp, status, session)
                VALUES (
                    '{$student['regd_no']}',
                    '{$student['program']}',
                    '{$student['year']}',
                    '{$student['department']}',
                    '{$student['section']}',
                    '$course_code',
                    '$faculty_name',
                    '$timestamp',
                    '$status',
                    '$session'
                )
                ON DUPLICATE KEY UPDATE status='$status', timestamp='$timestamp', session='$session'
            ");
        }
    }

    file_put_contents("esp_scan_data.json", json_encode(["success" => false]));
    echo "<script>alert('✅ Attendance automatically uploaded after 6 minutes!');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Smart RFID Attendance Board</title>
<style>
body { background:#0b0c10; color:white; font-family:Arial; text-align:center; }
.container { width:90%; margin:30px auto; background:#111827; padding:20px; border-radius:12px; box-shadow:0 0 25px cyan; }
select,button { padding:8px 12px; border:none; border-radius:6px; margin:5px; }
button { background:cyan; color:black; font-weight:bold; cursor:pointer; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th,td { padding:10px; border:1px solid #333; }
th { background:#1f2937; }
.statusA { color:red; font-weight:bold; }
.statusP { color:lime; font-weight:bold; }
.highlight { background:rgba(0,255,255,0.2); transition:background 1s; }
.editbtn { background:#ffcc00; color:black; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; }
</style>
</head>

<body>
<div class="container">
<h1>Smart RFID Attendance Board</h1>
<p><b>Faculty:</b> <?= $faculty_name ?> (<?= $faculty_regd_no ?>)</p>
<p><?= date("d-m-Y h:i A") ?></p>

<!-- ✅ FILTER FORM -->
<form method="GET">
    <select name="program" required><option value="">Program</option><?php foreach($programs as $p) echo "<option>$p</option>"; ?></select>
    <select name="year" required><option value="">Year</option><?php foreach($years as $y) echo "<option>$y</option>"; ?></select>
    <select name="department" required><option value="">Department</option><?php foreach($departments as $d) echo "<option>$d</option>"; ?></select>
    <select name="section" required><option value="">Section</option><?php foreach($sections as $s) echo "<option>$s</option>"; ?></select>
    <select name="semester" required><option value="">Semester</option><?php foreach($semesters as $s) echo "<option>$s</option>"; ?></select>
    <select name="session" required><option value="">Session</option><?php foreach($sessions as $s) echo "<option>$s</option>"; ?></select>
    <button type="submit" name="load">Load Students</button>
</form>

<?php if(!empty($students)): ?>
<form method="POST" id="attendanceForm">
<input type="hidden" name="session" value="<?= htmlspecialchars($_GET['session'] ?? '1') ?>">
<table>
<tr><th>#</th><th>Regd No</th><th>Student Name</th><th>Status</th><th>Edit</th><th>Timestamp</th></tr>
<?php $i=1; foreach($students as $stu): ?>
<tr id="row_<?= $stu['regd_no'] ?>">
    <td><?= $i++ ?></td>
    <td><?= $stu['regd_no'] ?></td>
    <td><?= $stu['student_name'] ?></td>
    <td id="status_<?= $stu['regd_no'] ?>" class="statusA">A</td>
    <td>
        <button type="button" class="editbtn" onclick="enableEdit('<?= $stu['regd_no'] ?>')">✏️ Edit</button>
        <select id="edit_<?= $stu['regd_no'] ?>" style="display:none;" onchange="manualEdit('<?= $stu['regd_no'] ?>', this.value)">
            <option value="A">A</option>
            <option value="P">P</option>
        </select>
    </td>
    <td id="time_<?= $stu['regd_no'] ?>">--</td>
    <input type="hidden" name="status_<?= $stu['regd_no'] ?>" id="input_<?= $stu['regd_no'] ?>" value="A">
</tr>
<?php endforeach; ?>
</table>
<br>
<button type="submit" name="submit_attendance" id="submitBtn" style="background:lime; color:black; padding:10px 20px; border:none; border-radius:8px;">✅ Submit Attendance</button>
<p id="timer" style="font-weight:bold; color:yellow;"></p>
</form>
<?php endif; ?>
</div>

<!-- ✅ JAVASCRIPT -->
<script>
function enableEdit(regd) {
    document.getElementById('edit_' + regd).style.display = 'inline-block';
}
function manualEdit(regd, value) {
    const s = document.getElementById('status_' + regd);
    const input = document.getElementById('input_' + regd);
    if (s && input) {
        s.textContent = value;
        s.className = (value === 'P') ? 'statusP' : 'statusA';
        input.value = value;
    }
}

// ✅ Update from RFID JSON
setInterval(async ()=>{
    try {
        const res = await fetch('esp_scan_data.json?' + Date.now());
        if (!res.ok) return;
        const data = await res.json();
        if (data.success) {
            const regd = data.regd_no, t = data.timestamp;
            const s = document.getElementById('status_'+regd);
            const row = document.getElementById('row_'+regd);
            const input = document.getElementById('input_'+regd);
            const time = document.getElementById('time_'+regd);
            if (s && input && s.textContent === 'A') {
                s.textContent = 'P';
                s.className = 'statusP';
                input.value = 'P';
                time.textContent = t;
                row.classList.add('highlight');
                setTimeout(()=>row.classList.remove('highlight'),2000);
            }
        }
    } catch(e) {}
}, 2000);

// ✅ Auto-submit after 6 minutes
let totalTime = 6 * 60;
const timerDisplay = document.getElementById('timer');
const form = document.getElementById('attendanceForm');
function updateTimer() {
    const min = Math.floor(totalTime / 60);
    const sec = totalTime % 60;
    timerDisplay.textContent = `⏳ Auto-submitting in ${min}:${sec.toString().padStart(2,'0')}`;
    if (totalTime <= 0) {
        timerDisplay.textContent = "⏰ Auto-submitting attendance...";
        form.submit(); // submit after 6 min
    } else totalTime--;
}
setInterval(updateTimer, 1000);
</script>
</body>
</html>
