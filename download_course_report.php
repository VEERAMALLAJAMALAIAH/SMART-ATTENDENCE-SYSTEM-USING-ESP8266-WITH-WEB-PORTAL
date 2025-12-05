<?php
session_start();

if (!isset($_SESSION['csv_data']) || !isset($_SESSION['csv_headers'])) {
    die("No report data available for download.");
}

$csv_data = $_SESSION['csv_data'];
$csv_headers = $_SESSION['csv_headers'];

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="course_code_report.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, $csv_headers);

foreach ($csv_data as $row) {
    fputcsv($output, $row);
}
fclose($output);
exit;
?>
