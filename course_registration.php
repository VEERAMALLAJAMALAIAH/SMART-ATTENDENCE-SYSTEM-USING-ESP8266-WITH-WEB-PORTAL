<?php
session_start();
include 'db_connect.php';

// ensure faculty login
if (!isset($_SESSION['faculty_regd_no'])) {
    header("Location: faculty_login.html?msg=" . urlencode("Please login first"));
    exit;
}

$faculty_regd = $_SESSION['faculty_regd_no'];

// fetch faculty name
$stmt = $conn->prepare("SELECT name FROM faculty WHERE regd_no=? LIMIT 1");
$stmt->bind_param("s", $faculty_regd);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$faculty_name = $res['name'] ?? 'Unknown Faculty';

// create table if not exists
$conn->query("
CREATE TABLE IF NOT EXISTS course_registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255),
    course_code VARCHAR(100),
    faculty_name VARCHAR(255),
    faculty_regd_no VARCHAR(100),
    program VARCHAR(100),
    department VARCHAR(150),
    year VARCHAR(10),
    semester VARCHAR(10),
    section VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $program = $_POST['program'];
    $department = $_POST['department'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $section = $_POST['section'];

    $ins = $conn->prepare("INSERT INTO course_registration 
        (course_name, course_code, faculty_name, faculty_regd_no, program, department, year, semester, section)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $ins->bind_param("sssssssss", $course_name, $course_code, $faculty_name, $faculty_regd, $program, $department, $year, $semester, $section);

    if ($ins->execute()) {
        $msg = "✅ Course Registered Successfully!";
    } else {
        $msg = "❌ Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Course Registration</title>
<style>
body {font-family: 'Segoe UI', sans-serif; margin:0; background:#f0f4ff; color:#222;}
.container {max-width:700px; margin:50px auto; background:white; padding:25px; border-radius:10px; box-shadow:0 6px 20px rgba(0,0,0,0.15);}
h2 {text-align:center; color:#333;}
form {display:grid; grid-template-columns:1fr 1fr; gap:15px;}
label {font-weight:600; margin-bottom:4px; display:block;}
input,select,button {width:100%; padding:8px; border-radius:6px; border:1px solid #ccc;}
button {grid-column:span 2; background:#007bff; color:white; border:none; cursor:pointer;}
button:hover {background:#0056b3;}
.msg {text-align:center; margin-bottom:10px; font-weight:bold;}
</style>
</head>
<body>
<div class="container">
  <h2>📘 Course Registration</h2>
  <?php if(isset($msg)) echo "<div class='msg'>$msg</div>"; ?>
  <form method="POST">
    <div>
      <label>Course Name</label>
      <input type="text" name="course_name" required>
    </div>
    <div>
      <label>Course Code</label>
      <input type="text" name="course_code" required>
    </div>
    <div>
      <label>Faculty Name</label>
      <input type="text" value="<?php echo htmlspecialchars($faculty_name); ?>" readonly>
    </div>
    <div>
      <label>Faculty Regd. No</label>
      <input type="text" value="<?php echo htmlspecialchars($faculty_regd); ?>" readonly>
    </div>
    <div>
      <label>Program</label>
      <select name="program" required>
        <option value="">--Select--</option>
        <option>B.TECH</option>
        <option>M.TECH</option>
        <option>MBA</option>
      </select>
    </div>
    <div>
      <label>Department</label>
      <select name="department" required>
        <option value="">--Select--</option>
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
        <option value="">--Select--</option>
        <option>I</option><option>II</option><option>III</option><option>IV</option>
      </select>
    </div>
    <div>
      <label>Semester</label>
      <select name="semester" required>
        <option value="">--Select--</option><option>I</option><option>II</option>
      </select>
    </div>
    <div>
      <label>Section</label>
      <select name="section" required>
        <option value="">--Select--</option>
        <option>A</option><option>B</option><option>C</option><option>D</option>
      </select>
    </div>
    <button type="submit">Register Course</button>
  </form>
</div>
</body>
</html>
