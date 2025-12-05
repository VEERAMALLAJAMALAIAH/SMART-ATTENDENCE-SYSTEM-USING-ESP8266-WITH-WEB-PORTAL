<?php
// =======================================================
// ✅ DEPENDENCIES & DATABASE CONNECTION
// =======================================================
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veeramalla_attendance_portal";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("❌ DB Connection Failed: " . $conn->connect_error); }

session_start();

// =======================================================
// ✅ FETCH FILTERS
// =======================================================
$program = $_POST['program'] ?? '';
$year = $_POST['year'] ?? '';
$department = $_POST['department'] ?? '';
$section = $_POST['section'] ?? '';
$semester = $_POST['semester'] ?? '';
$from_date = $_POST['from_date'] ?? '';
$to_date = $_POST['to_date'] ?? '';
$download_type = $_POST['download_type'] ?? 'pdf'; // pdf or csv

if (empty($from_date) || empty($to_date)) {
    die("❌ Please select a valid date range.");
}

// =======================================================
// ✅ GET DATES BETWEEN RANGE
// =======================================================
$dates_sql = "
    SELECT DISTINCT DATE(timestamp) as date
    FROM attendance_records
    WHERE DATE(timestamp) BETWEEN '$from_date' AND '$to_date'
    ORDER BY DATE(timestamp) ASC
";
$dates_result = $conn->query($dates_sql);
$dates = [];
while ($row = $dates_result->fetch_assoc()) { $dates[] = $row['date']; }

// =======================================================
// ✅ GET STUDENTS FROM studentregistration
// =======================================================
$students_sql = "
    SELECT regd_no, student_name
    FROM studentregistration
    WHERE program='$program' AND year='$year' 
    AND department='$department' AND section='$section' 
    AND semester='$semester'
    ORDER BY regd_no ASC
";
$students = $conn->query($students_sql);

if ($students->num_rows == 0 || count($dates) == 0) {
    die("<p>No attendance data found for the selected filters.</p>");
}

// =======================================================
// ✅ GENERATE TABLE DATA
// =======================================================
$tableHTML = "
<h2 style='text-align:center;'>LAKI REDDY BALI REDDY COLLEGE OF ENGINEERING</h2>
<h3 style='text-align:center;'>Department of $department</h3>
<h4 style='text-align:center;'>Weekly Attendance Report ($from_date to $to_date)</h4>
<br>
<table border='1' cellspacing='0' cellpadding='5' width='100%'>
<tr style='background:#2c3e50;color:white;'>
<th>S.No</th><th>Regd No</th><th>Name</th>";

foreach ($dates as $d) {
    $formatted = date("d-M", strtotime($d));
    $tableHTML .= "<th>$formatted</th>";
}
$tableHTML .= "<th>Total Present</th></tr>";

$sno = 1;
while ($stu = $students->fetch_assoc()) {
    $regd = $stu['regd_no'];
    $name = $stu['student_name'];
    $total = 0;
    $tableHTML .= "<tr><td>$sno</td><td>$regd</td><td>$name</td>";

    $count = 1;
    foreach ($dates as $d) {
        $check_sql = "
            SELECT id FROM attendance_records
            WHERE regd_no='$regd' 
            AND DATE(timestamp)='$d' 
            AND status='P'
        ";
        $check = $conn->query($check_sql);
        if ($check->num_rows > 0) {
            $tableHTML .= "<td style='text-align:center;'>$count</td>";
            $total++; $count++;
        } else {
            $tableHTML .= "<td style='color:red;font-weight:bold;text-align:center;'>A</td>";
        }
    }

    $tableHTML .= "<td style='text-align:center;'><b>$total</b></td></tr>";
    $sno++;
}
$tableHTML .= "</table>";

// =======================================================
// ✅ PDF DOWNLOAD (Using mPDF)
// =======================================================
if ($download_type == 'pdf') {
    $mpdf = new Mpdf(['orientation' => 'L']); // Landscape mode for wide table
    $mpdf->SetTitle('Weekly Attendance Report');
    $mpdf->WriteHTML($tableHTML);
    $mpdf->Output("Weekly_Attendance_Report.pdf", "D"); // Force download
    exit;
}

// =======================================================
// ✅ CSV DOWNLOAD (Using PhpSpreadsheet)
// =======================================================
elseif ($download_type == 'csv') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $col = 'A';

    // Header Row
    $sheet->setCellValue('A1', 'S.No')
          ->setCellValue('B1', 'Regd No')
          ->setCellValue('C1', 'Name');
    $colIndex = 4;
    foreach ($dates as $d) {
        $sheet->setCellValueByColumnAndRow($colIndex++, 1, date("d-M", strtotime($d)));
    }
    $sheet->setCellValueByColumnAndRow($colIndex, 1, 'Total Present');

    // Student Rows
    $rowNum = 2;
    $sno = 1;
    $students->data_seek(0); // Reset pointer

    while ($stu = $students->fetch_assoc()) {
        $regd = $stu['regd_no'];
        $name = $stu['student_name'];
        $total = 0;
        $colIndex = 1;

        $sheet->setCellValueByColumnAndRow($colIndex++, $rowNum, $sno);
        $sheet->setCellValueByColumnAndRow($colIndex++, $rowNum, $regd);
        $sheet->setCellValueByColumnAndRow($colIndex++, $rowNum, $name);

        $count = 1;
        foreach ($dates as $d) {
            $check_sql = "
                SELECT id FROM attendance_records
                WHERE regd_no='$regd' 
                AND DATE(timestamp)='$d' 
                AND status='P'
            ";
            $check = $conn->query($check_sql);
            if ($check->num_rows > 0) {
                $sheet->setCellValueByColumnAndRow($colIndex++, $rowNum, $count);
                $total++; $count++;
            } else {
                $sheet->setCellValueByColumnAndRow($colIndex++, $rowNum, 'A');
            }
        }
        $sheet->setCellValueByColumnAndRow($colIndex, $rowNum, $total);
        $sno++; $rowNum++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = "Weekly_Attendance_Report.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $writer->save("php://output");
    exit;
}
?>
