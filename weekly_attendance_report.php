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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📅 Weekly Attendance Report</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f9f9f9;
    padding: 20px;
}
h2, h3 {
    text-align: center;
    margin: 5px 0;
}
.header {
    text-align: center;
    font-size: 20px;
    font-weight: bold;
}
form {
    text-align: center;
    margin-bottom: 20px;
}
select, input[type="date"] {
    padding: 6px;
    margin-right: 10px;
}
button {
    padding: 8px 16px;
    background-color: #007BFF;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}
button:hover {
    background-color: #0056b3;
}
.download-container {
    text-align: right;
    margin-top: -30px;
    margin-bottom: 10px;
}
.download-btn {
    background-color: #28a745;
    margin-left: 10px;
}
table {
    border-collapse: collapse;
    width: 100%;
    background: white;
    margin-bottom: 30px;
}
th, td {
    border: 1px solid #ddd;
    padding: 6px;
    text-align: center;
}
th {
    background-color: #007BFF;
    color: white;
}
.absent {
    color: red;
    font-weight: bold;
}
.page-break {
    page-break-after: always;
}
</style>
</head>
<body>

<h2>📅 Weekly Attendance Report</h2>

<form method="POST">
    <label>Program:</label>
    <select name="program" required>
        <option value="">Select</option>
        <?php
        $result = $conn->query("SELECT DISTINCT program FROM studentregistration ORDER BY program");
        while ($r = $result->fetch_assoc()) echo "<option value='{$r['program']}'>{$r['program']}</option>";
        ?>
    </select>

    <label>Year:</label>
    <select name="year" required>
        <option value="">Select</option>
        <?php
        $result = $conn->query("SELECT DISTINCT year FROM studentregistration ORDER BY year");
        while ($r = $result->fetch_assoc()) echo "<option value='{$r['year']}'>{$r['year']}</option>";
        ?>
    </select>

    <label>Department:</label>
    <select name="department" required>
        <option value="">Select</option>
        <?php
        $result = $conn->query("SELECT DISTINCT department FROM studentregistration ORDER BY department");
        while ($r = $result->fetch_assoc()) echo "<option value='{$r['department']}'>{$r['department']}</option>";
        ?>
    </select>

    <label>Section:</label>
    <select name="section" required>
        <option value="">Select</option>
        <?php
        $result = $conn->query("SELECT DISTINCT section FROM studentregistration ORDER BY section");
        while ($r = $result->fetch_assoc()) echo "<option value='{$r['section']}'>{$r['section']}</option>";
        ?>
    </select>

    <label>Semester:</label>
    <select name="semester" required>
        <option value="">Select</option>
        <?php
        $result = $conn->query("SELECT DISTINCT semester FROM studentregistration ORDER BY semester");
        while ($r = $result->fetch_assoc()) echo "<option value='{$r['semester']}'>{$r['semester']}</option>";
        ?>
    </select>

    <br><br>

    <label>From:</label>
    <input type="date" name="from_date" required>
    <label>To:</label>
    <input type="date" name="to_date" required>

    <button type="submit" name="generate">Generate Report</button>
</form>

<?php
if (isset($_POST['generate'])) {
    $program = $_POST['program'];
    $year = $_POST['year'];
    $department = $_POST['department'];
    $section = $_POST['section'];
    $semester = $_POST['semester'];
    $from = $_POST['from_date'];
    $to = $_POST['to_date'];

    // ✅ Get all distinct dates between selected range
    $date_query = "
        SELECT DISTINCT DATE(timestamp) AS date
        FROM attendance_records
        WHERE DATE(timestamp) BETWEEN '$from' AND '$to'
        ORDER BY DATE(timestamp) ASC
    ";
    $dates_result = $conn->query($date_query);
    $dates = [];
    while ($row = $dates_result->fetch_assoc()) {
        $dates[] = $row['date'];
    }

    // ✅ Get filtered students
    $students_sql = "
        SELECT regd_no, student_name, department
        FROM studentregistration
        WHERE program='$program' AND year='$year' AND department='$department'
        AND section='$section' AND semester='$semester'
        ORDER BY regd_no ASC
    ";
    $students_result = $conn->query($students_sql);

    if ($students_result->num_rows > 0 && count($dates) > 0) {
        $_SESSION['weekly_pdf'] = [];

        foreach ($dates as $current_date) {
            $data_page = [];
            $students_result->data_seek(0);

            echo "<div class='page'>";
            echo "<div class='header'>LAKI REDDY BALI REDDY COLLEGE OF ENGINEERING</div>";
            echo "<h3>Department of $department</h3>";
            echo "<h3>Date: " . date("d-M-Y", strtotime($current_date)) . "</h3>";

            echo "<div class='download-container'>
                    <form method='POST' action='download_weekly_report.php' style='display:inline;'>
                        <input type='hidden' name='date' value='$current_date'>
                        <input type='hidden' name='program' value='$program'>
                        <input type='hidden' name='year' value='$year'>
                        <input type='hidden' name='department' value='$department'>
                        <input type='hidden' name='section' value='$section'>
                        <input type='hidden' name='semester' value='$semester'>
                        <button class='download-btn' name='type' value='pdf'>⬇ PDF</button>
                        <button class='download-btn' name='type' value='csv'>⬇ CSV</button>
                    </form>
                  </div>";

            echo "<table>
                    <tr>
                        <th>S.No</th>
                        <th>Regd No</th>
                        <th>Name</th>
                        <th>S1</th><th>S2</th><th>S3</th><th>S4</th><th>S5</th><th>S6</th>
                        <th>Total</th>
                    </tr>";

            $sno = 1;
            while ($stu = $students_result->fetch_assoc()) {
                $regd = $stu['regd_no'];
                $name = $stu['student_name'];
                $total = 0;
                echo "<tr><td>$sno</td><td>$regd</td><td>$name</td>";
                $row_data = [$sno, $regd, $name];

                for ($session = 1; $session <= 6; $session++) {
                    $check_sql = "
                        SELECT id FROM attendance_records
                        WHERE regd_no='$regd' 
                        AND DATE(timestamp)='$current_date' 
                        AND Session='$session'
                        AND status='P'
                    ";
                    $check = $conn->query($check_sql);
                    if ($check->num_rows > 0) {
                        echo "<td>$session</td>";
                        $row_data[] = $session;
                        $total++;
                    } else {
                        echo "<td class='absent'>A</td>";
                        $row_data[] = "A";
                    }
                }
                echo "<td><b>$total</b></td></tr>";
                $row_data[] = $total;
                $data_page[] = $row_data;
                $sno++;
            }
            echo "</table><div class='page-break'></div></div>";

            $_SESSION['weekly_pdf'][$current_date] = [
                'department' => $department,
                'data' => $data_page
            ];
        }
    } else {
        echo "<p style='text-align:center;color:red;'>⚠ No records found for the selected filters or date range.</p>";
    }
}
?>
</body>
</html>
