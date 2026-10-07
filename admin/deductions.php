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

$pageTitle = 'Authorized deductions';
$active    = 'deductions';
$eyebrow   = 'Payroll administration';
$subtitle  = 'Apply deductions to employees. Every deduction is logged.';
require '../includes/header.php';
?>

<?php if ($msg) echo "<div class='msg success'>$msg</div>"; ?>
<?php if ($err) echo "<div class='msg error'>" . htmlspecialchars($err) . "</div>"; ?>

<div class="split-form">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Apply a deduction</h3>
                <span class="small">Every deduction is recorded with a reason.</span>
            </div>
        </div>
        <form method="POST" class="panel-body form-stack" onsubmit="return confirm('Apply this deduction?');">
            <div class="field">
                <label for="employee_id">Employee</label>
                <select name="employee_id" id="employee_id" required>
                    <option value="">Select employee</option>
                    <?php foreach ($employees as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="amount">Amount (₱)</label>
                <input type="number" step="0.01" min="0.01" name="amount" id="amount" placeholder="0.00" required>
            </div>
            <div class="field">
                <label for="reason">Reason</label>
                <textarea name="reason" id="reason" maxlength="255" placeholder="Reason" required></textarea>
            </div>
            <button type="submit" class="btn-block">Apply Deduction</button>
        </form>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Deductions history</h3>
                <span class="small">Latest 100 deductions.</span>
            </div>
        </div>
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
    </div>
</div>

<?php require '../includes/footer.php'; ?>