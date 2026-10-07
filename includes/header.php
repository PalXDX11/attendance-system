<?php
/*
 * Shared layout (top part) for all admin pages.
 * Before including this file, set:
 *   $pageTitle  - big title of the page
 *   $active     - which sidebar item is highlighted (dashboard, live, employees, deductions, payroll)
 *   $eyebrow    - small label above the title (optional)
 *   $subtitle   - small text under the title (optional)
 */
date_default_timezone_set('Asia/Manila');

if (!function_exists('initials')) {
    function initials($name) {
        $parts = preg_split('/\s+/', trim($name));
        $i = strtoupper(substr($parts[0] ?? '', 0, 1));
        if (count($parts) > 1) {
            $i .= strtoupper(substr(end($parts), 0, 1));
        }
        return $i !== '' ? $i : '?';
    }
}

$pageTitle = $pageTitle ?? 'Admin';
$active    = $active ?? '';
$eyebrow   = $eyebrow ?? '';
$subtitle  = $subtitle ?? '';

// key => [label, link, svg icon shapes]
$nav = [
    'dashboard' => ['Dashboard', 'dashboard.php',
        '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>'],
    'live' => ['Live Monitor', 'live.php',
        '<circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/>'],
    'employees' => ['Employees', 'employees.php',
        '<path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M21 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
    'deductions' => ['Deductions', 'deductions.php',
        '<circle cx="12" cy="12" r="9"/><line x1="8" y1="12" x2="16" y2="12"/>'],
    'payroll' => ['Payroll Summary', 'payroll.php',
        '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/><circle cx="16" cy="14.5" r="1"/>'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> | Oppa's Samgyeopsal</title>
    <link rel="stylesheet" href="/attendance/assets/style.css">

    <link rel="icon" href="/attendance/assets/images/logo.jpeg">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="sb-brand">
            <img src="/attendance/assets/images/logo.jpeg" alt="Logo" class="sb-logo-img">
            <div>
                <strong>Oppa's Samgyeopsal</strong>
                <span>ATTENDANCE &amp; PAYROLL</span>
            </div>
        </div>

        <div class="sb-label">WORKSPACE</div>
        <nav class="sb-nav">
            <?php foreach ($nav as $key => $item): ?>
                <a href="<?= $item[1] ?>" class="<?= $key === $active ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><?= $item[2] ?></svg>
                    <?= $item[0] ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sb-user">
            <span class="avatar">A</span>
            <div>
                <strong>admin</strong>
                <span>Administrator</span>
            </div>
            <a href="../logout.php">Logout</a>
        </div>
    </aside>

    <div class="main">
        <div class="page-top">
            <span class="eyebrow">Attendance &amp; Payroll Portal</span>
            <span class="date-pill"><?= date('M j, Y') ?></span>
        </div>

        <div class="content">
            <div class="page-head">
                <?php if ($eyebrow): ?><span class="eyebrow"><?= htmlspecialchars($eyebrow) ?></span><?php endif; ?>
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
                <?php if ($subtitle): ?><p><?= htmlspecialchars($subtitle) ?></p><?php endif; ?>
            </div>