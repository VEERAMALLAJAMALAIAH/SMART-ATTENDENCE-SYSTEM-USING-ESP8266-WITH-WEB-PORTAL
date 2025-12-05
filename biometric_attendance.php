<?php
// biometric_attendance.php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentRegNo = $_POST['student_regno'] ?? '';
    $facultyId = $_POST['faculty_id'] ?? '';
    $status = $_POST['status'] ?? 'Present';
    $timestamp = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO attendance (student_regno, faculty_id, status, timestamp)
                            VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $studentRegNo, $facultyId, $status, $timestamp);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Attendance Recorded Successfully"]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Biometric Attendance</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background:#f6f8fb; padding:30px; }
h2 { color:#2d4cff; }
#device-status { padding:10px 15px; border-radius:8px; width:fit-content; }
.connected { background:#d4edda; color:#155724; }
.notconnected { background:#f8d7da; color:#721c24; }
button { padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; }
#capture-btn { background:#007bff; color:white; }
#submit-btn { background:#28a745; color:white; margin-left:10px; }
#result { margin-top:20px; font-weight:bold; }
</style>
<script>
let captured = false;

async function checkDevice() {
    const statusDiv = document.getElementById('device-status');
    const btn = document.getElementById('capture-btn');
    try {
        const urls = [
            "http://localhost:11100/rd/info",              // Common RD port
            "http://localhost:11100/startek/rd/info",      // Startek alt path
            "http://localhost:11100/mfs100/rd/info"        // Mantra alt path
        ];
        let found = false;
        for (let url of urls) {
            const res = await fetch(url);
            if (res.ok) {
                const text = await res.text();
                if (text.includes("DeviceInfo")) {
                    statusDiv.innerHTML = "✅ Biometric Device Connected";
                    statusDiv.className = "connected";
                    btn.disabled = false;
                    found = true;
                    break;
                }
            }
        }
        if (!found) throw new Error("Not Found");
    } catch (e) {
        statusDiv.innerHTML = "❌ Connect Biometric Device";
        statusDiv.className = "notconnected";
        btn.disabled = true;
    }
}

async function captureFingerprint() {
    const urls = [
        "http://localhost:11100/rd/capture",
        "http://localhost:11100/startek/rd/capture",
        "http://localhost:11100/mfs100/rd/capture"
    ];
    const pidOptions = `
      <PidOptions ver="1.0">
        <Opts fCount="1" format="0" pidVer="2.0" timeout="10000" posh="UNKNOWN" env="P"/>
      </PidOptions>`;
    document.getElementById("result").innerHTML = "Capturing... please place finger.";
    for (let url of urls) {
        try {
            const res = await fetch(url, {
                method: "CAPTURE",
                headers: { "Content-Type": "text/xml" },
                body: pidOptions
            });
            if (res.ok) {
                const xml = await res.text();
                if (xml.includes("<PidData")) {
                    document.getElementById("result").innerHTML =
                        "✅ Fingerprint Captured Successfully!";
                    captured = true;
                    document.getElementById("submit-btn").disabled = false;
                    return;
                }
            }
        } catch (e) { /* try next */ }
    }
    document.getElementById("result").innerHTML = "❌ Capture Failed. Try Again.";
}

async function submitAttendance() {
    if (!captured) return alert("Please capture fingerprint first.");
    const form = document.getElementById("attendance-form");
    const data = new FormData(form);
    const res = await fetch("biometric_attendance.php", { method:"POST", body:data });
    const json = await res.json();
    if (json.success) {
        document.getElementById("result").innerHTML =
            "✅ " + json.message + " at " + new Date().toLocaleTimeString();
    } else {
        document.getElementById("result").innerHTML = "❌ Error saving attendance";
    }
}

window.onload = checkDevice;
</script>
</head>
<body>
<h2>🧾 Biometric Attendance (Auto-Detect: Mantra / Startek)</h2>
<div id="device-status" class="notconnected">Checking Device...</div><br>

<form id="attendance-form">
  <label>Student Regd No:</label>
  <input type="text" name="student_regno" required><br><br>
  <label>Faculty ID:</label>
  <input type="text" name="faculty_id" required><br><br>
  <input type="hidden" name="status" value="Present">
</form>

<button id="capture-btn" onclick="captureFingerprint()" disabled>Capture Fingerprint</button>
<button id="submit-btn" onclick="submitAttendance()" disabled>Submit Attendance</button>
<p id="result"></p>
</body>
</html>
