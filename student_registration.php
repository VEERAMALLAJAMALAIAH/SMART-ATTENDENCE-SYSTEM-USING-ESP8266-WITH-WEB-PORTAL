<?php
include 'db_connect.php';
$msg = "";

// Save student registration to DB
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = $_POST['student_name'];
    $regd_no = $_POST['regd_no'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $program = $_POST['program'];
    $department = $_POST['department'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $section = $_POST['section'];

    // handle photo
    $photo_path = "";
    if (!empty($_FILES['photo']['name'])) {
        $upload_dir = "uploads/student_photos/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_name = time() . "_" . basename($_FILES["photo"]["name"]);
        $target_file = $upload_dir . $file_name;
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
        $photo_path = $target_file;
    }

    $stmt = $conn->prepare("INSERT INTO studentregistration 
        (student_name, regd_no, phone, email, program, department, year, semester, section, photo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $student_name, $regd_no, $phone, $email, $program, $department, $year, $semester, $section, $photo_path);
    
    if ($stmt->execute()) {
        $msg = "✅ Student Registered Successfully!";
    } else {
        $msg = "❌ Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📘 Student Registration — Veeramalla Attendance Portal</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
  background: linear-gradient(135deg, #0a192f, #172a45);
  color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}
.container {
  width:900px;
  background: rgba(255,255,255,0.06);
  border-radius: 15px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(1,0,0,0.4);
}
h2 {
  text-align: center;
  margin-bottom: 25px;
  color: #ffcc66;
}
.option-buttons {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-bottom: 25px;
}
.option-buttons button {
  padding: 12px 25px;
  border: none;
  border-radius:10px;
  background: linear-gradient(90deg,#ff9933,#ffcc33);
  color: #111;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}
.option-buttons button:hover {
  transform: translateY(-3px);
  background: linear-gradient(90deg,#ffcc33,#ffd966);
}
form {
  display: none;
}
form.active {
  display: block;
}
.grid {
  display: grid;
  grid-template-columns: repeat(4,2fr);
  gap: 20px;
}
label {
  display: block;
  font-size: 14px;
  margin-bottom: 10px;
}
input, select {
  width: 100%;
  padding: 8px;
  border-radius: 8px;
  border: none;
  background: rgba(0,10,10,20);
  color: #fff;
}
button.submit-btn {
  margin-top: 20px;
  background: #28a745;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 18px;
  font-weight: bold;
  cursor: pointer;
}
button.submit-btn:hover {
  background: #34d058;
}
.message {
  text-align: center;
  margin-bottom: 15px;
  color: #00ff99;
}
</style>
</head>
<body>

<div class="container">
  <h2>📘 Student Registration Portal</h2>
  <div class="option-buttons">
    <button type="button" onclick="showForm('registration')">🧾 Student Registration</button>
   <a href="biometric_registration.php" class="btn btn-success">Register for Biometric</a>
  </div>

  <?php if ($msg): ?>
    <div class="message"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <form id="registrationForm" method="post" enctype="multipart/form-data" class="active">
    <div class="grid">
      <div>
        <label>Student Name</label>
        <input type="text" name="student_name" required>
      </div>
      <div>
        <label>Regd. No</label>
        <input type="text" name="regd_no" required>
      </div>
      <div>
        <label>Phone Number</label>
        <input type="text" name="phone" required>
      </div>
      <div>
        <label>Email ID</label>
        <input type="email" name="email" required>
      </div>
      <div>
        <label>Program</label>
        <select name="program" required>
          <option value="">--Select Program--</option>
          <option>B.TECH</option>
          <option>M.TECH</option>
          <option>MBA</option>
        </select>
      </div>
      <div>
        <label>Department</label>
        <select name="department" required>
          <option value="">--Select Department--</option>
          <option>Aerospace Engineering</option>
          <option>Artificial Intelligence & Data Science</option>
          <option>Civil Engineering</option>
          <option>Computer Science & Engineering</option>
          <option>Computer Science & Engineering (AI & ML)</option>
          <option>Electrical & Electronics Engineering</option>
          <option>Electronics & Communication Engineering</option>
          <option>Information Technology</option>
          <option>Mechanical Engineering</option>
          <option>Master of Business Administration (MBA)</option>
        </select>
      </div>
      <div>
        <label>Year</label>
        <select name="year" required>
        <option value="">--Select year--</option>
          <option>I</option><option>II</option><option>III</option><option>IV</option>
        </select>
      </div>
      <div>
        <label>Semester</label>
        <select name="semester" required>
          <option value="">--Select semester--</option>
          <option>I</option><option>II</option>
        </select>
      </div>
      <div>
        <label>Section</label>
        <select name="section" required>
        <option value="">--Select section--</option>
          <option>A</option><option>B</option><option>C</option><option>D</option><option>E</option>
        </select>
      </div>
      <div style="grid-column: 1 / span 2;">
        <label>Upload Photo</label>
        <input type="file" name="photo" accept="image/*">
      </div>
    </div>
    <button type="submit" class="submit-btn">Submit</button>
  </form>
</div>

<script>
function showForm(formType) {
  document.getElementById('registrationForm').classList.add('active');
}
function openBiometric() {
  window.location.href = "student_biometric.php";
}
</script>
</body>
</html>
