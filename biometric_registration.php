<?php
// ✅ Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veeramalla_attendance_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Biometric Registration</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { background: #f5f5f5; }
    .container { margin-top: 40px; }
    img { border-radius: 8px; }
  </style>
</head>
<body>
<div class="container">
  <h2 class="text-center mb-4">Student Biometric Registration</h2>

  <!-- Filter Form -->
  <div class="card p-4 shadow-sm">
    <form id="filterForm">
      <div class="row mb-3">
        <div class="col-md-3">
          <label>Program</label>
          <select id="program" class="form-control">
            <option value="">Select Program</option>
            <option>B.Tech</option>
            <option>M.Tech</option>
            <option>Diploma</option>
          </select>
        </div>
        <div class="col-md-2">
          <label>Year</label>
          <select id="year" class="form-control">
            <option value="">Select Year</option>
            <option>I</option>
            <option>II</option>
            <option>III</option>
            <option>IV</option>
          </select>
        </div>
        <div class="col-md-4">
          <label>Department</label>
          <select id="department" class="form-control">
            <option value="">Select Department</option>
            <option>Computer Science Engineering</option>
            <option>Electronics & Communication Engineering</option>
            <option>Mechanical Engineering</option>
          </select>
        </div>
        <div class="col-md-2">
          <label>Section</label>
          <select id="section" class="form-control">
            <option value="">Select Section</option>
            <option>A</option>
            <option>B</option>
            <option>C</option>
          </select>
        </div>
        <div class="col-md-1 d-flex align-items-end">
          <button type="button" class="btn btn-primary w-100" id="loadStudents">Load</button>
        </div>
      </div>
    </form>

    <!-- Regd No Dropdown -->
    <div class="mb-3">
      <label>Select Student (Regd No)</label>
      <select id="studentSelect" class="form-control">
        <option value="">Please select filters and click Load</option>
      </select>
    </div>

    <!-- Student Profile -->
    <div id="studentProfile" class="border rounded p-3" style="display:none;">
      <h5 class="mb-3 text-primary">Student Profile</h5>
      <div class="row">
        <div class="col-md-4 text-center">
          <img id="studentPhoto" src="" alt="Photo" class="img-thumbnail mb-2" width="150">
        </div>
        <div class="col-md-8">
          <p><b>Regd No:</b> <span id="s_regdno"></span></p>
          <p><b>Name:</b> <span id="s_name"></span></p>
          <p><b>Program:</b> <span id="s_program"></span></p>
          <p><b>Year:</b> <span id="s_year"></span></p>
          <p><b>Department:</b> <span id="s_dept"></span></p>
          <p><b>Section:</b> <span id="s_section"></span></p>
          <p><b>Phone:</b> <span id="s_phone"></span></p>
          <p><b>Email:</b> <span id="s_email"></span></p>
        </div>
      </div>
      <button id="registerBiometric" class="btn btn-success mt-3">Register Biometric</button>
    </div>
  </div>
</div>

<!-- JS (AJAX) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

  // Load Students
  $('#loadStudents').click(function(){
    var program = $('#program').val();
    var year = $('#year').val();
    var dept = $('#department').val();
    var section = $('#section').val();

    if(program && year && dept && section){
      $.ajax({
        url: 'fetch_students.php',
        type: 'GET',
        data: { program: program, year: year, department: dept, section: section },
        success: function(response){
          $('#studentSelect').html(response);
        }
      });
    } else {
      alert('Please select all filters.');
    }
  });

  // Load Profile
  $('#studentSelect').change(function(){
    var regdno = $(this).val();
    if(regdno){
      $.ajax({
        url: 'fetch_student_profile.php',
        type: 'GET',
        data: { regdno: regdno },
        dataType: 'json',
        success: function(data){
          if(data){
            $('#studentProfile').show();
            $('#s_regdno').text(data.regd_no);
            $('#s_name').text(data.student_name);
            $('#s_program').text(data.program);
            $('#s_year').text(data.year);
            $('#s_dept').text(data.department);
            $('#s_section').text(data.section);
            $('#s_phone').text(data.phone);
            $('#s_email').text(data.email);
            $('#studentPhoto').attr('src', data.photo ? data.photo : 'default.jpg');
          }
        }
      });
    } else {
      $('#studentProfile').hide();
    }
  });

  // Register Biometric Button
  $('#registerBiometric').click(function(){
    var regdno = $('#s_regdno').text();
    if(regdno){
      $.ajax({
        url: 'save_biometric.php',
        type: 'POST',
        data: { regdno: regdno },
        success: function(resp){
          alert(resp);
        }
      });
    }
  });

});
</script>
</body>
</html>
