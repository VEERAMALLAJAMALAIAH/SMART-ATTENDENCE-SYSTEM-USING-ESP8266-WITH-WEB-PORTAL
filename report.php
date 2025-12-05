<?php
// =======================================================
// ✅ DATABASE CONNECTION
// =======================================================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veeramalla_attendance_portal";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("❌ DB connection failed: " . $conn->connect_error); }
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Reports</title>
<style>
    body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f9; }
    header { background: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
    h1 { font-size: 22px; margin: 0; }
    nav a { color: white; margin-left: 15px; text-decoration: none; font-weight: bold; }
    .container { padding: 20px; }
    .tabs { margin-bottom: 20px; }
    .tab-button {
        background: #34495e; color: white; padding: 10px 15px; border: none;
        border-radius: 8px; margin-right: 8px; cursor: pointer;
    }
    .tab-button.active { background: #1abc9c; }
    .tab-content { display: none; background: white; padding: 20px; border-radius: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background: #2c3e50; color: white; }
    .absent { color: red; font-weight: bold; }
    select, input, button { padding: 8px; border-radius: 5px; border: 1px solid #ccc; margin: 5px; }
    button[type=submit] { background: #1abc9c; color: white; border: none; cursor: pointer; }
</style>
<script>
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabId).style.display = 'block';
    event.target.classList.add('active');
}
</script>
</head>

<body onload="showTab('daily')">

<header>
  <h1>📊 Attendance Report Dashboard</h1>
  <nav>
    <a href="dashboard.php">🏠 Back to Dashboard</a>
  </nav>
</header>

<div class="container">

  <!-- ✅ REPLACED ONLY THIS TABS SECTION -->
  <div class="tabs">
    <button class="tab-button active" onclick="showTab('daily')">Daily Report</button>
    <button class="tab-button" onclick="showTab('course')">Course Code Report</button>
    <button class="tab-button" onclick="showTab('weekly')">Weekly / Monthly Report</button>
    <button class="tab-button" onclick="showTab('late')">Late Comers</button>
  </div>

  <!-- ================= DAILY REPORT TAB ================= -->
  <div id="daily" class="tab-content">
    <h3>📅 Daily Attendance Report</h3>
    <form method="POST">
      <input type="hidden" name="report_type" value="daily">

      <label>Program:</label>
      <select name="program" required>
        <option value="">-- Program --</option>
        <option>B.TECH</option><option>M.TECH</option><option>MBA</option><option>Diploma</option>
      </select>

      <label>Year:</label>
      <select name="year" required>
        <option value="">-- Year --</option><option>I</option><option>II</option><option>III</option><option>IV</option>
      </select>

      <label>Department:</label>
      <select name="department" required>
        <option value="">-- Department --</option>
        <option>Computer Science and Engineering</option>
        <option>Electronics & Communication Engineering</option>
        <option>Electrical and Electronics Engineering</option>
        <option>Mechanical Engineering</option>
        <option>Civil Engineering</option>
        <option>Information Technology</option>
        <option>Artificial Intelligence and Machine Learning</option>
      </select>

      <label>Section:</label>
      <select name="section" required>
        <option value="">-- Section --</option>
        <option>A</option><option>B</option><option>C</option><option>D</option><option>E</option>
      </select>

      <label>Semester:</label>
      <select name="semester" required>
        <option value="">-- Semester --</option><option>I</option><option>II</option>
      </select>

      <label>Date:</label>
      <input type="date" name="date" required>
      <button type="submit" name="load">Load Report</button>
    </form>

    <?php
    if (isset($_POST['load']) && $_POST['report_type'] == 'daily') {
        $program = $_POST['program'];
        $year = $_POST['year'];
        $department = $_POST['department'];
        $section = $_POST['section'];
        $semester = $_POST['semester'];
        $date = $_POST['date'];
        $selected_date = date('Y-m-d', strtotime($date));

        $sql_students = "
            SELECT regd_no, student_name
            FROM studentregistration
            WHERE program='$program' AND year='$year' 
            AND department='$department' AND section='$section' 
            AND semester='$semester' ORDER BY regd_no ASC
        ";
        $students = $conn->query($sql_students);

        if ($students->num_rows > 0) {
            echo "<table><tr>
                    <th>S.No</th><th>Regd No</th><th>Name</th>
                    <th>S1</th><th>S2</th><th>S3</th><th>S4</th><th>S5</th><th>S6</th>
                    <th>Total Present</th></tr>";

            $sno = 1;
            while ($row = $students->fetch_assoc()) {
                $regd = $row['regd_no'];
                $name = $row['student_name'];
                $total = 0;
                echo "<tr><td>$sno</td><td>$regd</td><td>$name</td>";

                for ($i=1;$i<=6;$i++) {
                    $q = "SELECT id FROM attendance_records 
                          WHERE regd_no='$regd' AND Session='$i' 
                          AND DATE(timestamp)='$selected_date' AND status='P'";
                    $r = $conn->query($q);
                    if ($r->num_rows > 0) { echo "<td>$i</td>"; $total++; }
                    else { echo "<td class='absent'>A</td>"; }
                }
                echo "<td><b>$total</b></td></tr>";
                $sno++;
            }
            echo "</table>";
        } else {
            echo "<p>No students found for selected filters.</p>";
        }
    }
    ?>
  </div>

  <!-- ================= COURSE CODE TAB ================= -->
  <div id="course" class="tab-content">
   <button onclick="window.location.href='course_code_report.php'">📘 Course Code Report</button>
   <li><a href="course_code_report.php">📘 Course Code Report</a></li>
 

    <p>Coming soon — will display attendance filtered by course code (from course_registration).</p>
  </div>

  <!-- ================= MONTHLY TAB ================= -->
  <div id="weekly" class="tab-content">
    <h3>🗓️ Weekly / Monthly Attendance Summary</h3>
    <li><a href="weekly_attendance_report.php">📅 Weekly Attendance Report</a></li>

    <p>Coming soon — will calculate % attendance for week or month range.</p>
  </div>

  <!-- ================= LATE COMERS TAB ================= -->
  <div id="late" class="tab-content">
    <h3>⏰ Late Comers Report</h3>
     <a href="late_comers_report.php" class="btn btn-outline-danger">
  🚨 Late Comers Report
     </a>

    <p>Coming soon — will show students who marked after class start time.</p>
  </div>

</div>
</body>
</html>
