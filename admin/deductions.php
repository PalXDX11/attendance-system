<?php
require '../auth.php';
require '../db.php';
require_once '../includes/functions.php';
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
        logAction($pdo, $uid, 'add_deduction', "{$emp['name']}: -" . number_format($amount, 2) . " ($reason)");
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
                <div class="input-icon">
                <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <select name="employee_id" id="employee_id" required>
                    <option value="">Select employee</option>
                    <?php foreach ($employees as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                </div>
            </div>
            <div class="field">
                <label for="amount">Amount (₱)</label>
                <div class="input-icon">
                <svg class="icon" viewBox="0 0 24 24"><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M8 6h8"/><path d="M16 14v4"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/></svg>
                <input type="number" step="0.01" min="0.01" name="amount" id="amount" placeholder="0.00" required>
                </div>
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
                <span class="small">Auditable record of every applied deduction.</span>
            </div>
        </div>
        <?php if ($history): ?>
        <div class="table-wrap">
            <table>
                <tr><th>Employee</th><th>Reason</th><th>Amount</th><th>Date</th><th>Authorized</th></tr>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td><span class="emp-name"><?= htmlspecialchars($h['name']) ?></span></td>
                    <td class="muted-cell"><?= htmlspecialchars($h['reason']) ?></td>
                    <td class="amount-strong">₱<?= number_format($h['amount'], 2) ?></td>
                    <td class="muted-cell" title="<?= htmlspecialchars($h['created_at']) ?>"><?= date('M d, Y', strtotime($h['created_at'])) ?></td>
                    <td class="muted-cell"><?= htmlspecialchars($h['username']) ?></td>
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