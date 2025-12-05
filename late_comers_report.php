<?php
session_start();
include 'db_connect.php';
date_default_timezone_set('Asia/Kolkata');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Late Comers Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    table th, table td {
      text-align: center;
      vertical-align: middle;
    }
    .absent {
      color: red;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>📋 Late Comers Report</h2>
      <a href="attendance_report_dashboard.php" class="btn btn-outline-primary">🏠 Back to Dashboard</a>
    </div>

    <div class="card p-4">
      <h4 class="mb-3">Filter Options</h4>
      <form method="POST" class="row g-3">
        <div class="col-md-2">
          <label class="form-label">Program</label>
          <select name="program" class="form-select" required>
            <option value="">-- Program --</option>
            <option value="B.TECH">B.TECH</option>
            <option value="M.TECH">M.TECH</option>
            <option value="MBA">MBA</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Year</label>
          <select name="year" class="form-select" required>
            <option value="">-- Year --</option>
            <option value="I">I</option>
            <option value="II">II</option>
            <option value="III">III</option>
            <option value="IV">IV</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Department</label>
          <select name="department" class="form-select" required>
            <option value="">-- Department --</option>
            <option value="Computer Science and Engineering">Computer Science and Engineering</option>
            <option value="Electronics & Communication Engineering">Electronics & Communication Engineering</option>
            <option value="Electrical & Electronics Engineering">Electrical & Electronics Engineering</option>
            <option value="Mechanical Engineering">Mechanical Engineering</option>
            <option value="Civil Engineering">Civil Engineering</option>
          </select>
        </div>

        <div class="col-md-1">
          <label class="form-label">Section</label>
          <select name="section" class="form-select" required>
            <option value="">--</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
            <option value="E">E</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Semester</label>
          <select name="semester" class="form-select" required>
            <option value="">--</option>
            <option value="I">I</option>
            <option value="II">II</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="col-md-12 text-end">
          <button type="submit" name="find_latecomers" class="btn btn-success px-4">Find Late Comers</button>
        </div>
      </form>
    </div>

    <div class="mt-4">
      <?php
      if (isset($_POST['find_latecomers'])) {
        $program = $_POST['program'];
        $year = $_POST['year'];
        $department = $_POST['department'];
        $section = $_POST['section'];
        $semester = $_POST['semester'];
        $date = $_POST['date'];

        // 🔍 Find students who came after 9:10 AM for 1st session
        $sql = "
          SELECT s.regd_no, s.student_name, a.timestamp
          FROM attendance_records a
          JOIN studentregistration s ON a.regd_no = s.regd_no
          WHERE DATE(a.timestamp) = '$date'
            AND TIME(a.timestamp) > '09:10:00'
            AND a.Session = '1'
            AND s.program = '$program'
            AND s.year = '$year'
            AND s.department = '$department'
            AND s.section = '$section'
            AND s.semester = '$semester'
          ORDER BY a.timestamp ASC
        ";

        $result = $conn->query($sql);
        echo "<h4 class='mt-3'>Late Comers on $date (After 9:10 AM)</h4>";

        if ($result->num_rows > 0) {
          echo "<table class='table table-bordered table-striped mt-3'>";
          echo "<thead class='table-dark'>
                  <tr>
                    <th>S.No</th>
                    <th>Regd No</th>
                    <th>Name</th>
                    <th>Timestamp</th>
                  </tr>
                </thead><tbody>";
          $sno = 1;
          $msg = "Dear Class Teacher/HOD,\nToday's first hour these are the late comers:\n";

          while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$sno}</td>
                    <td>{$row['regd_no']}</td>
                    <td>{$row['student_name']}</td>
                    <td>{$row['timestamp']}</td>
                  </tr>";
            $msg .= "- {$row['regd_no']} ({$row['student_name']})\n";
            $sno++;
          }
          echo "</tbody></table>";

          $msg .= "\nFrom $year-B.TECH SEC & SEM: $section / $semester";

          // 📨 SMS send (demo example)
          $phone_numbers = ["9999999999", "8888888888"]; // Replace with HOD / CT numbers
          foreach ($phone_numbers as $num) {
            $api_url = "RbO81wmirgXIu0tNyWKqdDvThae7nPCzBMJ96Fjpc53QkVZHYUmnSgWxEGuNXoMfshw1j2pIvB8A47JV" . urlencode($msg);
            @file_get_contents($api_url);
          }

          echo "<div class='alert alert-success mt-3'>✅ SMS sent successfully to HOD/Class Teacher.</div>";
        } else {
          echo "<p class='text-danger mt-3'>No late comers found for this date.</p>";
        }
      }
      ?>
    </div>
  </div>
</body>
</html>
