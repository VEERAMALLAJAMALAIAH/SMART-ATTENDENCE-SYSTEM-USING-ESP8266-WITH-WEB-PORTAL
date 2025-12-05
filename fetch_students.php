<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);   // ← ✅ Don't forget this semicolon!

require_once 'db_connect.php';  // include the database connection file

if (!isset($pdo)) {
    echo json_encode(["success" => false, "message" => "Database connection not initialized"]);
    exit;
}

// Get JSON data from frontend
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['program'], $input['year'], $input['department'], $input['section'])) {
    echo json_encode(["success" => false, "message" => "Invalid or empty JSON payload"]);
    exit;
}

$program = trim($input['program']);
$year = trim($input['year']);
$department = trim($input['department']);
$section = trim($input['section']);

try {
    $stmt = $pdo->prepare("
        SELECT regdno, name 
        FROM studentregistration
        WHERE program = ? AND year = ? AND department = ? AND section = ?
    ");
    $stmt->execute([$program, $year, $department, $section]);

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "students" => $students
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
