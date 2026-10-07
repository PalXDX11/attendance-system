<?php
require '../auth.php';
require '../db.php';
require_once '../includes/functions.php';
requireAdmin();

$msg = "";
$msgType = "success";
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $name = trim($_POST['name']);
            $fid  = (int)$_POST['fingerprint_id'];
            $rate = (float)$_POST['hourly_rate'];
            try {
                $pdo->prepare("INSERT INTO employees (name, fingerprint_id, hourly_rate) VALUES (?, ?, ?)")
                    ->execute([$name, $fid, $rate]);
                logAction($pdo, $uid, 'add_employee', "Added $name (FP $fid, rate $rate)");
                $msg = htmlspecialchars($name) . " was added.";
            } catch (PDOException $e) {
                $msg = "Error: that fingerprint ID is already in use.";
                $msgType = "error";
            }
            break;

        case 'update_rate':
            $id   = (int)$_POST['id'];
            $rate = (float)$_POST['hourly_rate'];
            if ($rate < 0) {
                $msg = "Rate cannot be negative.";
                $msgType = "error";
            } else {
                $stmt = $pdo->prepare("SELECT name, hourly_rate FROM employees WHERE id = ?");
                $stmt->execute([$id]);
                $emp = $stmt->fetch();
                if ($emp) {
                    $pdo->prepare("UPDATE employees SET hourly_rate = ? WHERE id = ?")->execute([$rate, $id]);
                    logAction($pdo, $uid, 'update_rate', "{$emp['name']}: {$emp['hourly_rate']} -> $rate");
                    $msg = "Updated rate for " . htmlspecialchars($emp['name']) . ".";
                }
            }
            break;

        case 'toggle_status':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("SELECT name, status FROM employees WHERE id = ?");
            $stmt->execute([$id]);
            $emp = $stmt->fetch();
            if ($emp) {
                $new = $emp['status'] === 'active' ? 'inactive' : 'active';
                $pdo->prepare("UPDATE employees SET status = ? WHERE id = ?")->execute([$new, $id]);
                logAction($pdo, $uid, 'set_status', "{$emp['name']}: {$emp['status']} -> $new");
                $msg = htmlspecialchars($emp['name']) . " is now $new.";
            }
            break;
    }
}

$employees = $pdo->query("SELECT * FROM employees ORDER BY id")->fetchAll();

$pageTitle = 'Employees';
$active    = 'employees';
$eyebrow   = 'Payroll administration';
$subtitle  = 'Add employees, edit hourly rates, and activate or deactivate accounts.';
require '../includes/header.php';
?>

<?php if ($msg) echo "<div class='msg $msgType'>$msg</div>"; ?>

<div class="split-rates">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Employees</h3>
                <span class="small">Showing <?= count($employees) ?> employees</span>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <tr><th>Employee / ID</th><th>Fingerprint ID</th><th>Base rate</th><th>Status</th><th>Actions</th></tr>
                <?php foreach ($employees as $e): ?>
                <tr class="<?= $e['status'] === 'inactive' ? 'inactive' : '' ?>">
                    <td>
                        <div class="emp">
                            <span class="avatar"><?= htmlspecialchars(initials($e['name'])) ?></span>
                            <div>
                                <span class="emp-name"><?= htmlspecialchars($e['name']) ?></span>
                                <span class="emp-sub">EMP-<?= str_pad($e['id'], 4, '0', STR_PAD_LEFT) ?></span>
                            </div>
                        </div>
                    </td>
                    <td><?= $e['fingerprint_id'] ?></td>
                    <td>
                        <form method="POST" class="inline rate-form">
                            <input type="hidden" name="action" value="update_rate">
                            <input type="hidden" name="id" value="<?= $e['id'] ?>">
                            ₱<input type="number" step="0.01" min="0" name="hourly_rate" value="<?= $e['hourly_rate'] ?>" required>
                            <span class="per">/ hr</span>
                            <button type="submit" class="sm soft">Save</button>
                        </form>
                    </td>
                    <td><span class="badge <?= $e['status'] ?>"><?= ucfirst($e['status']) ?></span></td>
                    <td>
                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id" value="<?= $e['id'] ?>">
                            <?php if ($e['status'] === 'active'): ?>
                                <button type="submit" class="sm gray">Deactivate</button>
                            <?php else: ?>
                                <button type="submit" class="sm green">Activate</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Add employee</h3>
                <span class="small">Register a new employee and base rate.</span>
            </div>
        </div>
        <form method="POST" class="panel-body form-stack">
            <input type="hidden" name="action" value="add">
            <div class="field">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" placeholder="Name" required>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="fingerprint_id">Fingerprint ID</label>
                    <input type="number" id="fingerprint_id" name="fingerprint_id" placeholder="Fingerprint ID" required>
                </div>
                <div class="field">
                    <label for="hourly_rate">Base rate (₱/hr)</label>
                    <input type="number" step="0.01" id="hourly_rate" name="hourly_rate" placeholder="Rate/hr" value="50" required>
                </div>
            </div>
            <button type="submit" class="btn-block">Add Employee</button>
        </form>
    </div>
</div>

<?php require '../includes/footer.php'; ?>