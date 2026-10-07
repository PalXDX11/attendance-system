<?php
require '../auth.php';
require '../db.php';
requireAdmin();

$msg = "";
$msgType = "success";
$uid = $_SESSION['user_id'];

function logAction($pdo, $uid, $action, $details) {
    $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)")
        ->execute([$uid, $action, $details]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
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
    }

    if ($action === 'update_rate') {
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
    }

    if ($action === 'toggle_status') {
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

<form method="POST" class="card form-row">
    <input type="hidden" name="action" value="add">
    <input type="text" name="name" placeholder="Name" required>
    <input type="number" name="fingerprint_id" placeholder="Fingerprint ID" required>
    <input type="number" step="0.01" name="hourly_rate" placeholder="Rate/hr" value="50" required>
    <button type="submit">Add Employee</button>
</form>

<div class="table-wrap">
    <table>
        <tr><th>ID</th><th>Name</th><th>Fingerprint ID</th><th>Rate/hr</th><th>Status</th><th>Action</th></tr>
        <?php foreach ($employees as $e): ?>
        <tr class="<?= $e['status'] === 'inactive' ? 'inactive' : '' ?>">
            <td><?= $e['id'] ?></td>
            <td>
                <div class="emp">
                    <span class="avatar"><?= htmlspecialchars(initials($e['name'])) ?></span>
                    <?= htmlspecialchars($e['name']) ?>
                </div>
            </td>
            <td><?= $e['fingerprint_id'] ?></td>
            <td>
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="update_rate">
                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                    ₱<input type="number" step="0.01" min="0" name="hourly_rate" value="<?= $e['hourly_rate'] ?>" required>
                    <button type="submit" class="sm">Save</button>
                </form>
            </td>
            <td><span class="badge <?= $e['status'] ?>"><?= $e['status'] ?></span></td>
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

<?php require '../includes/footer.php'; ?>