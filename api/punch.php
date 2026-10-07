<?php
require '../db.php';
header('Content-Type: application/json');

$fingerprintId = $_POST['fingerprint_id'] ?? null;
if (!$fingerprintId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No fingerprint_id provided..']);
    exit;
}

// Hanapin ang employee
$stmt = $pdo->prepare("SELECT * FROM employees WHERE fingerprint_id = ? AND status = 'active'");
$stmt->execute([$fingerprintId]);
$emp = $stmt->fetch();

if (!$emp) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Employee not found.']);
    exit;
}

// May open na attendance ba?
$stmt = $pdo->prepare("SELECT * FROM attendance_logs WHERE employee_id = ? AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1");
$stmt->execute([$emp['id']]);
$open = $stmt->fetch();

if ($open) {
    // CLOCK OUT
    $pdo->prepare("UPDATE attendance_logs SET clock_out = NOW() WHERE id = ?")->execute([$open['id']]);

    $stmt = $pdo->prepare("SELECT TIMESTAMPDIFF(SECOND, clock_in, clock_out) AS secs FROM attendance_logs WHERE id = ?");
    $stmt->execute([$open['id']]);
    $secs = $stmt->fetch()['secs'];
    $pay = round(($secs / 3600) * $emp['hourly_rate'], 2);

    echo json_encode([
        'status' => 'ok',
        'action' => 'clock_out',
        'name' => $emp['name'],
        'hours' => round($secs / 3600, 2),
        'pay' => $pay
    ]);
} else {
    // CLOCK IN
    $pdo->prepare("INSERT INTO attendance_logs (employee_id, clock_in) VALUES (?, NOW())")->execute([$emp['id']]);

    echo json_encode([
        'status' => 'ok',
        'action' => 'clock_in',
        'name' => $emp['name']
    ]);
}