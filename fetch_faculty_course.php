<?php
$conn = new mysqli("localhost", "root", "", "veeramalla_attendance_portal");
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

$date = date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Board</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
  body {
    background: linear-gradient(145deg, #e6e9f0, #eef1f5);
    min-height: 100vh;
  }
  .card-3d {
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    border-radius: 15px;
    background: #fff;
    transition: transform 0.2s ease-in-out;
  }
  .card-3d:hover {
    transform: translateY(-5px);
  }
  table th, table td {
    vertical-align: middle;
  }
  .fw-bold { font-weight: 600; }
</style>
</head>

<body class="p-4">
<div class="container-fluid">
  <h2 class="text-center mb-4 fw-bold">Attendance Board</h2>

  <!-- Faculty/Course Info -->
  <div id="facultyInfo" class="card card-3d p-3 mb-4 text-end">
    <strong>Date:</strong> <?= $date ?><br>
    <strong>Faculty Name:</strong> <span id="facultyName">---</span><br>
    <strong>Faculty Regd No:</strong> <span id="facultyRegd">---</span><br>
    <strong>Course:</strong> <span id="courseName">---</span> (<span id="courseCode">---</span>)
  </div>

  <!-- Filters -->
  <div class="card card-3d p-4 mb-4">
    <div class="row g-3 align-items-end">
      <div class="col-md-2 col-sm-6">
        <label class="form-label fw-bold">Program</label>
        <select id="program" class="form-select">
          <option value="">Select Program</option>
          <option>B.Tech</option>
          <option>M.Tech</option>
          <option>MBA</option>
          <option>Diploma</option>
        </select>
      </div>

      <div class="col-md-1 col-sm-6">
        <label class="form-label fw-bold">Year</label>
        <select id="year" class="form-select">
          <option value="">Select</option>
          <option>I</option><option>II</option><option>III</option><option>IV</option>
        </select>
      </div>

      <div class="col-md-3 col-sm-12">
        <label class="form-label fw-bold">Department</label>
        <select id="department" class="form-select">
          <option value="">Select Department</option>
          <option value="CSE">Computer Science and Engineering (CSE)</option>
          <option value="AIML">Artificial Intelligence and Machine Learning (AIML)</option>
          <option value="AIDS">Artificial Intelligence and Data Science (AIDS)</option>
          <option value="IT">Information Technology (IT)</option>
          <option value="ECE">Electronics and Communication Engineering (ECE)</option>
          <option value="EEE">Electrical and Electronics Engineering (EEE)</option>
          <option value="MECH">Mechanical Engineering (MECH)</option>
          <option value="CIVIL">Civil Engineering (CIVIL)</option>
          <option value="CHEM">Chemical Engineering (CHEM)</option>
          <option value="AGRI">Agricultural Engineering (AGRI)</option>
          <option value="MBA">Master of Business Administration (MBA)</option>
          <option value="MCA">Master of Computer Applications (MCA)</option>
          <option value="PHARM">Pharmacy (PHARM)</option>
          <option value="BIO">Biotechnology (BIO)</option>
        </select>
      </div>

      <div class="col-md-1 col-sm-4">
        <label class="form-label fw-bold">Section</label>
        <select id="section" class="form-select">
          <option value="">Select</option>
          <option>A</option><option>B</option><option>C</option><option>D</option><option>E</option>
        </select>
      </div>

      <div class="col-md-2 col-sm-6">
        <label class="form-label fw-bold">Semester</label>
        <select id="semester" class="form-select">
          <option value="">Select</option>
          <option>I</option><option>II</option><option>III</option><option>IV</option>
          <option>V</option><option>VI</option><option>VII</option><option>VIII</option>
        </select>
      </div>

      <div class="col-md-3 col-sm-12">
        <button id="loadStudents" class="btn btn-primary w-100 fw-bold">Load Students</button>
      </div>
    </div>
  </div>

  <!-- Attendance Table -->
  <div class="card card-3d p-3">
    <table class="table table-bordered table-striped text-center align-middle" id="attendanceTable">
      <thead class="table-dark">
        <tr>
          <th style="width:5%">S.No</th>
          <th style="width:15%">Regd No</th>
          <th style="width:30%">Name of Student</th>
          <th style="width:25%">Timestamp</th>
          <th style="width:15%">Status (P/A)</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>

<script>
// Load Faculty & Course + Students
$("#loadStudents").on("click", function() {
  let data = {
    program: $("#program").val(),
    year: $("#year").val(),
    department: $("#department").val(),
    section: $("#section").val(),
    semester: $("#semester").val()
  };

  // 1️⃣ Fetch faculty/course data
  $.post("fetch_faculty_course.php", data, function(response) {
    let info = JSON.parse(response);
    $("#facultyName").text(info.faculty_name || "---");
    $("#facultyRegd").text(info.faculty_regdno || "---");
    $("#courseName").text(info.course_name || "---");
    $("#courseCode").text(info.course_code || "---");
  });

  // 2️⃣ Fetch student list
  $.post("fetch_students_attendance.php", data, function(response) {
    $("#attendanceTable tbody").html(response);
  });
});

// Auto-refresh table every 10 seconds
setInterval(() => {
  $("#loadStudents").click();
}, 10000);
</script>

</body>
</html>
