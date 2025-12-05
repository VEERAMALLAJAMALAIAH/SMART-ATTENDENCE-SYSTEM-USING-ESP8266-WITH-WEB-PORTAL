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
    die("❌ Database connection failed: " . $conn->connect_error);
}

session_start();
$faculty_name = $_SESSION['faculty_name'] ?? '';
$faculty_regdno = $_SESSION['faculty_regdno'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📘 Course Code Attendance Report</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f8f9fa;
    margin: 30px;
}
h3 { color: #2c3e50; }
form {
    margin-bottom: 20px;
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
}
select, input[type=date], button {
    padding: 8px;
    margin: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
}
th, td {
    border: 1px solid #ddd;
    text-align: center;
    padding: 8px;
}
th {
    background: #007bff;
    color: white;
}
.absent {
    color: red;
    font-weight: bold;
}
.back-btn {
    background: #28a745;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.back-btn:hover {
    background: #218838;
}
</style>
</head>
<body>

<button class="back-btn" onclick="window.location.href='dashboard.php'">⬅ Back to Dashboard</button>

<h3>📘 Course Code-wise Attendance Report</h3>

<form method="POST">
    <input type="hidden" name="report_type" value="course">

    <?php
    // Load course codes for logged-in faculty
    $courses_sql = "SELECT DISTINCT course_code 
                    FROM course_registration 
                    WHERE faculty_name='$faculty_name' OR faculty_regdno='$faculty_regdno'";
    $courses_result = $conn->query($courses_sql);
    ?>

    <label>Course Code:</label>
    <select name="course_code" required>
        <option value="">-- Select Course Code --</option>
        <?php
        if ($courses_result && $courses_result->num_rows > 0) {
            while ($course = $courses_result->fetch_assoc()) {
                echo "<option value='{$course['course_code']}'>{$course['course_code']}</option>";
            }
        } else {
            echo "<option disabled>No Courses Found</option>";
        }
        ?>
    </select>

    <label>From:</label>
    <input type="date" name="from_date" required>

    <label>To:</label>
    <input type="date" name="to_date" required>

    <button type="submit" name="load_course">Load Report</button>
</form>

<?php
if (isset($_POST['load_course']) && $_POST['report_type'] == 'course') {
    $course_code = $_POST['course_code'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];

    // ✅ Fetch distinct dates in range
    $dates_sql = "
        SELECT DISTINCT DATE(timestamp) as date
        FROM attendance_records
        WHERE course_code='$course_code'
        AND DATE(timestamp) BETWEEN '$from_date' AND '$to_date'
        ORDER BY DATE(timestamp) ASC
    ";
    $dates_result = $conn->query($dates_sql);
    $dates = [];
    while ($row = $dates_result->fetch_assoc()) {
        $dates[] = $row['date'];
    }

    // ✅ Fetch students registered in this course
    $students_sql = "
        SELECT DISTINCT s.regd_no, s.student_name
        FROM studentregistration s
        JOIN attendance_records a ON s.regd_no = a.regd_no
        WHERE a.course_code='$course_code'
        ORDER BY s.regd_no ASC
    ";
    $students = $conn->query($students_sql);

    // ✅ Generate table
    if ($students && $students->num_rows > 0 && count($dates) > 0) {
        echo "<table><tr><th>S.No</th><th>Regd No</th><th>Name</th>";
        foreach ($dates as $d) {
            $formatted = date('d-M', strtotime($d));
            echo "<th>$formatted</th>";
        }
        echo "<th>Total Present</th></tr>";

        $sno = 1;
        while ($stu = $students->fetch_assoc()) {
            $regd = $stu['regd_no'];
            $name = $stu['student_name'];
            $total = 0;
            $count = 1;

            echo "<tr><td>$sno</td><td>$regd</td><td>$name</td>";

            foreach ($dates as $d) {
                $check_sql = "
                    SELECT id FROM attendance_records
                    WHERE regd_no='$regd'
                    AND course_code='$course_code'
                    AND DATE(timestamp)='$d'
                    AND status='P'
                ";
                $check = $conn->query($check_sql);

                if ($check->num_rows > 0) {
                    echo "<td>$count</td>";
                    $total++;
                    $count++;
                } else {
                    echo "<td class='absent'>A</td>";
                }
            }

            echo "<td><b>$total</b></td></tr>";
            $sno++;
        }

        echo "</table>";
    } else {
        echo "<p>No attendance data found for the selected range.</p>";
    }
}
?>

</body>
</html>
