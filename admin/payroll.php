<?php
require '../auth.php';
require '../db.php';
requireAdmin();

date_default_timezone_set('Asia/Manila');

// Week runs Monday to Sunday
try {
    $base = new DateTime($_GET['start'] ?? 'today');
} catch (Exception $e) {
    $base = new DateTime('today');
}
$base->modify('monday this week')->setTime(0, 0, 0);

$weekStart = clone $base;
$weekEnd   = (clone $base)->modify('+7 days');          // exclusive
$prevWeek  = (clone $base)->modify('-7 days')->format('Y-m-d');
$nextWeek  = (clone $base)->modify('+7 days')->format('Y-m-d');
$thisWeek  = (new DateTime('today'))->modify('monday this week')->format('Y-m-d');

$s = $weekStart->format('Y-m-d H:i:s');
$e = $weekEnd->format('Y-m-d H:i:s');
$label = $weekStart->format('M d, Y') . ' - ' . (clone $weekEnd)->modify('-1 day')->format('M d, Y');

$stmt = $pdo->prepare("
    SELECT e.id, e.name, e.hourly_rate, e.status,
        COALESCE((SELECT SUM(TIMESTAMPDIFF(SECOND, a.clock_in, a.clock_out))
                    FROM attendance_logs a
                   WHERE a.employee_id = e.id AND a.clock_out IS NOT NULL
                     AND a.clock_in >= ? AND a.clock_in < ?), 0) AS secs,
        (SELECT COUNT(*) FROM attendance_logs a
          WHERE a.employee_id = e.id AND a.clock_out IS NULL
            AND a.clock_in >= ? AND a.clock_in < ?) AS open_count,
        COALESCE((SELECT SUM(d.amount) FROM deductions d
                   WHERE d.employee_id = e.id
                     AND d.created_at >= ? AND d.created_at < ?), 0) AS ded
    FROM employees e
    ORDER BY e.name
");
$stmt->execute([$s, $e, $s, $e, $s, $e]);
$all = $stmt->fetchAll();

// Show active employees, plus inactive ones only if they have activity this week
$rows = [];
$tot = ['secs' => 0, 'gross' => 0, 'ded' => 0, 'net' => 0];
foreach ($all as $r) {
    if ($r['status'] === 'inactive' && $r['secs'] == 0 && $r['ded'] == 0 && $r['open_count'] == 0) continue;
    $hours = $r['secs'] / 3600;
    $gross = round($hours * $r['hourly_rate'], 2);
    $ded   = (float)$r['ded'];
    $net   = $gross - $ded;
    $rows[] = $r + ['hours' => $hours, 'gross' => $gross, 'net' => $net];
    $tot['secs']  += $r['secs'];
    $tot['gross'] += $gross;
    $tot['ded']   += $ded;
    $tot['net']   += $net;
}

function peso($n) {
    return ($n < 0 ? '-₱' : '₱') . number_format(abs($n), 2);
}

$pageTitle = 'Payroll Summary (Weekly)';
$active    = 'payroll';
$eyebrow   = 'Payroll';
$subtitle  = 'Hours, gross pay, deductions, and net pay for each week.';
require '../includes/header.php';
?>

<div class="toolbar">
    <a class="btn gray" href="?start=<?= $prevWeek ?>">← Previous week</a>
    <span class="range"><?= htmlspecialchars($label) ?></span>
    <a class="btn gray" href="?start=<?= $nextWeek ?>">Next week →</a>
    <a class="btn" href="?start=<?= $thisWeek ?>">This week</a>
    <button onclick="window.print()">Print</button>
</div>

<div class="table-wrap">
    <table>
        <tr>
            <th>Employee</th>
            <th>Total hours</th>
            <th>Rate/hr</th>
            <th>Gross pay</th>
            <th>Deductions</th>
            <th>Net pay</th>
        </tr>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td>
                <?= htmlspecialchars($r['name']) ?>
                <?php if ($r['status'] === 'inactive') echo ' <span class="badge inactive">inactive</span>'; ?>
                <?php if ($r['open_count'] > 0) echo '<br><span class="note">Still clocked in (not included yet)</span>'; ?>
            </td>
            <td><?= number_format($r['hours'], 2) ?></td>
            <td>₱<?= number_format($r['hourly_rate'], 2) ?></td>
            <td><?= peso($r['gross']) ?></td>
            <td class="ded"><?= $r['ded'] > 0 ? '-' . peso($r['ded']) : peso(0) ?></td>
            <td class="net <?= $r['net'] < 0 ? 'negative' : '' ?>"><?= peso($r['net']) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="total">
            <td>TOTAL</td>
            <td><?= number_format($tot['secs'] / 3600, 2) ?></td>
            <td></td>
            <td><?= peso($tot['gross']) ?></td>
            <td class="ded"><?= $tot['ded'] > 0 ? '-' . peso($tot['ded']) : peso(0) ?></td>
            <td><?= peso($tot['net']) ?></td>
        </tr>
    </table>
</div>
<?php if (!$rows): ?><p class="small">No records for this week.</p><?php endif; ?>

<p class="small" style="margin-top:14px;">
    Week runs Monday to Sunday. Only completed shifts (with clock out) are counted, based on the clock-in date.
    Deductions are counted by the date they were applied.
</p>

<?php require '../includes/footer.php'; ?>