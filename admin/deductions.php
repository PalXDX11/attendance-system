<?php
require '../auth.php';
require '../db.php';
requireAdmin();

$msg = "";
$err = "";
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empId  = (int)($_POST['employee_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');

    $stmt = $pdo->prepare("SELECT name FROM employees WHERE id = ?");
    $stmt->execute([$empId]);
    $emp = $stmt->fetch();

    if (!$emp) {
        $err = "Employee not found.";
    } elseif ($amount <= 0) {
        $err = "Amount must be greater than 0.";
    } elseif ($reason === '') {
        $err = "Reason is required.";
    } else {
        $pdo->prepare("INSERT INTO deductions (employee_id, amount, reason, applied_by) VALUES (?, ?, ?, ?)")
            ->execute([$empId, $amount, $reason, $uid]);
        $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'add_deduction', ?)")
            ->execute([$uid, "{$emp['name']}: -" . number_format($amount, 2) . " ($reason)"]);
        $msg = "Deduction of ₱" . number_format($amount, 2) . " applied to " . htmlspecialchars($emp['name']) . ".";
    }
}

$employees = $pdo->query("SELECT id, name FROM employees WHERE status = 'active' ORDER BY name")->fetchAll();

$history = $pdo->query("
    SELECT d.created_at, e.name, d.amount, d.reason, u.username
    FROM deductions d
    JOIN employees e ON e.id = d.employee_id
    JOIN users u ON u.id = d.applied_by
    ORDER BY d.created_at DESC, d.id DESC
    LIMIT 100
")->fetchAll();

$pageTitle = 'Deductions';
$active    = 'deductions';
$eyebrow   = 'Pay adjustments';
$subtitle  = 'Apply deductions to employees. Every deduction is logged.';
require '../includes/header.php';
?>

<?php if ($msg) echo "<div class='msg success'>$msg</div>"; ?>
<?php if ($err) echo "<div class='msg error'>" . htmlspecialchars($err) . "</div>"; ?>

<form method="POST" class="card form-row" onsubmit="return confirm('Apply this deduction?');">
    <select name="employee_id" required>
        <option value="">Select employee</option>
        <?php foreach ($employees as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount (₱)" required>
    <input type="text" name="reason" placeholder="Reason" maxlength="255" size="30" required>
    <button type="submit">Apply Deduction</button>
</form>

<h2>History</h2>
<?php if ($history): ?>
<div class="table-wrap">
    <table>
        <tr><th>Date</th><th>Employee</th><th>Amount</th><th>Reason</th><th>Applied by</th></tr>
        <?php foreach ($history as $h): ?>
        <tr>
            <td><?= htmlspecialchars($h['created_at']) ?></td>
            <td><?= htmlspecialchars($h['name']) ?></td>
            <td class="amount">-₱<?= number_format($h['amount'], 2) ?></td>
            <td><?= htmlspecialchars($h['reason']) ?></td>
            <td><?= htmlspecialchars($h['username']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php else: ?>
    <div class="empty">No deductions yet.</div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>