<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['faculty_regd_no'])) {
    header("Location: faculty_login.html?msg=" . urlencode("Please login"));
    exit;
}

$regd = $_SESSION['faculty_regd_no'];
$stmt = $conn->prepare("SELECT name, regd_no, photo FROM faculty WHERE regd_no = ? LIMIT 1");
$stmt->bind_param("s", $regd);
$stmt->execute();
$res = $stmt->get_result();
$faculty = $res->fetch_assoc();

$faculty_name = $faculty['name'] ?? $_SESSION['faculty_name'] ?? 'Faculty';
$faculty_regd = $faculty['regd_no'] ?? $regd;
$faculty_photo = !empty($faculty['photo']) ? $faculty['photo'] : 'uploads/faculty_photos/default_user.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Faculty Dashboard</title>
<style>
  body {
    font-family: 'Segoe UI', Roboto, sans-serif;
    margin: 0;
    background: linear-gradient(135deg, #2b1055, #7597de);
    color: #fff;
    min-height: 100vh;
    overflow-x: hidden;
  }
  header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 28px;
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
  }
  .profile {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .profile img {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
  }
  .grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    padding: 40px;
  }
  .card {
    background: rgba(255, 255, 255, 0.12);
    padding: 25px;
    border-radius: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25);
  }
  .card:hover {
    transform: translateY(-6px) scale(1.03);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
  }
  .icon {
    font-size: 64px;
    margin-bottom: 15px;
    color: #fff;
    text-shadow: 0 0 15px rgba(255,255,255,0.6);
  }
  .card h3 {
    color: #fff;
    font-weight: 600;
    letter-spacing: 0.5px;
  }
  .back {
    margin-left: 20px;
    padding: 8px 12px;
    border-radius: 8px;
    border: none;
    background: #ff6b6b;
    color: #fff;
    cursor: pointer;
    font-weight: bold;
  }
  iframe {
    width: 100%;
    height: 74vh;
    border: none;
    display: none;
    margin-top: 12px;
    border-radius: 8px;
    background: #fff;
  }
  #backBtn {
    display: none;
    margin: 18px 30px;
    padding: 8px 12px;
    border-radius: 8px;
    border: none;
    background: #fff;
    color: #333;
    cursor: pointer;
    font-weight: 600;
  }
</style>
</head>
<body>
  <header>
    <div>
      <h2 style="margin:0">🎓 LBRCE Attendance Portal</h2>
      <div style="opacity:0.9;font-size:13px">Welcome, <?php echo htmlspecialchars($faculty_name); ?></div>
    </div>

    <div class="profile">
      <div style="text-align:right">
        <div style="font-weight:700"><?php echo htmlspecialchars($faculty_name); ?></div>
        <div style="font-size:13px;opacity:0.9">ID: <?php echo htmlspecialchars($faculty_regd); ?></div>
      </div>
      <img src="<?php echo htmlspecialchars($faculty_photo); ?>" alt="photo">
      <form action="logout.php" method="post" style="margin:0">
        <button class="back">Logout</button>
      </form>
    </div>
  </header>

  <button id="backBtn" onclick="goBack()">⬅ Back to menu</button>

  <div class="grid" id="menuGrid">
    <div class="card" onclick="openPage('faculty_register.html')">
      <div class="icon">👩‍🏫</div>
      <h3>Faculty Registration</h3>
    </div>

    <div class="card" onclick="openPage('student_registration.php')">
      <div class="icon">🎓</div>
      <h3>Student Registration</h3>
    </div>
 

    <div class="card" onclick="openPage('course_registration.php')">
      <div class="icon">📘</div>
      <h3>Course Registration</h3>
    </div>

    <div class="card" onclick="openPage('attendance_board.php')">
      <div class="icon">🗓️</div>
      <h3>Attendance Board</h3>
    </div>

    <div class="card" onclick="openPage('report.php')">
      <div class="icon">📊</div>
      <h3>Reports</h3>
    </div>
  </div>

  <iframe id="contentFrame"></iframe>

<script>
function openPage(page){
  document.getElementById('menuGrid').style.display='none';
  document.getElementById('backBtn').style.display='inline-block';
  var f = document.getElementById('contentFrame');
  f.src = page;
  f.style.display = 'block';
}
function goBack(){
  document.getElementById('contentFrame').style.display='none';
  document.getElementById('backBtn').style.display='none';
  document.getElementById('menuGrid').style.display='grid';
}
</script>
</body>
</html>
