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
<title>📘 Course Code-wise Attendance Report</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    padding: 20px;
}
h2 {
    color: #333;
}
form {
    margin-bottom: 20px;
}
label {
    font-weight: bold;
    margin-right: 5px;
}
select, input[type="date"] {
    padding: 6px;
    margin-right: 10px;
}
button {
    padding: 7px 14px;
    background-color: #007BFF;
    border: none;
    color: white;
    cursor: pointer;
    border-radius: 5px;
}
button:hover {
    background-color: #0056b3;
}
table {
    border-collapse: collapse;
    width: 100%;
    background: white;
    margin-top: 20px;
}
table, th, td {
    border: 1px solid #ddd;
    text-align: center;
}
th {
    background: #007BFF;
    color: white;
    padding: 8px;
}
td {
    padding: 6px;
}
.absent {
    color: red;
    font-weight: bold;
}
.download-btn {
    background-color: #28a745;
    margin-top: 10px;
}
</style>
</head>
<body>

<h2>📘 Course Code-wise Attendance Report</h2>

<form method="POST">
    <input type="hidden" name="report_type" value="course">

    <?php
    // Load course codes for logged-in faculty
    $courses_sql = "SELECT DISTINCT course_code 
                    FROM course_registration 
                    WHERE faculty_name='$faculty_name' 
                    OR faculty_regd_no='$faculty_regdno'";
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

    // Get all distinct dates in selected range
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

    // Get all students registered for this course
    $students_sql = "
        SELECT DISTINCT s.regd_no, s.student_name
        FROM studentregistration s
        JOIN attendance_records a ON s.regd_no = a.regd_no
        WHERE a.course_code='$course_code'
        ORDER BY s.regd_no ASC
    ";
    $students = $conn->query($students_sql);

    if ($students->num_rows > 0 && count($dates) > 0) {
        // Start output buffering for download
        ob_start();

        echo "<table><tr><th>S.No</th><th>Regd No</th><th>Name</th>";

        foreach ($dates as $d) {
            $formatted = date("d-M", strtotime($d));
            echo "<th>$formatted</th>";
        }
        echo "<th>Total Present</th></tr>";

        $sno = 1;
        $csv_data = [];

        while ($stu = $students->fetch_assoc()) {
            $regd = $stu['regd_no'];
            $name = $stu['student_name'];
            $total = 0;

            echo "<tr><td>$sno</td><td>$regd</td><td>$name</td>";
            $row_data = [$sno, $regd, $name];

            $count = 1;
            foreach ($dates as $d) {
                $check_sql = "
                    SELECT id FROM attendance_records
                    WHERE regd_no='$regd' AND course_code='$course_code'
                    AND DATE(timestamp)='$d' AND status='P'
                ";
                $check = $conn->query($check_sql);

                if ($check->num_rows > 0) {
                    echo "<td>$count</td>";
                    $row_data[] = $count;
                    $total++;
                    $count++;
                } else {
                    echo "<td class='absent'>A</td>";
                    $row_data[] = "A";
                }
            }

            echo "<td><b>$total</b></td></tr>";
            $row_data[] = $total;
            $csv_data[] = $row_data;
            $sno++;
        }
        echo "</table>";

        // Save table data for CSV download
        $_SESSION['csv_data'] = $csv_data;
        $_SESSION['csv_headers'] = array_merge(['S.No', 'Regd No', 'Name'], $dates, ['Total Present']);

        echo "<form method='POST' action='download_course_report.php'>
                <button type='submit' class='download-btn'>⬇ Download CSV</button>
              </form>";

        ob_end_flush();
    } else {
        echo "<p>No attendance data found for the selected range.</p>";
    }
}
?>

</body>
</html>
