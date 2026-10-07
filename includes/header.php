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

require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Admin';
$active    = $active ?? '';
$eyebrow   = $eyebrow ?? '';
$subtitle  = $subtitle ?? '';

// key => [label, link, svg icon shapes]
$nav = [
    'dashboard' => ['Overview', 'dashboard.php',
        '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>'],
    'live' => ['Live attendance', 'live.php',
        '<path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01"/><path d="M15 9h.01"/>'],
    'deductions' => ['Authorized deductions', 'deductions.php',
        '<circle cx="12" cy="12" r="10"/><path d="M8 12h8"/>'],
    'employees' => ['Employees', 'employees.php',
        '<path d="M18 21a8 8 0 0 0-16 0"/><circle cx="10" cy="8" r="5"/><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/>'],
    'payroll' => ['Payroll summary', 'payroll.php',
        '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/><circle cx="16" cy="14.5" r="1"/>'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> | Oppa's Samgyeopsal</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="icon" href="../assets/images/logo.jpeg">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="sb-brand">
            <span class="brand-mark">OS</span>
            <div>
                <strong>Oppa's Samgyeopsal</strong>
                <span>People operations</span>
            </div>
        </div>

        <div class="sb-label">Workspace</div>
        <nav class="sb-nav">
            <?php foreach ($nav as $key => $item): ?>
                <a href="<?= $item[1] ?>" class="<?= $key === $active ? 'active' : '' ?>">
                    <svg class="icon" viewBox="0 0 24 24"><?= $item[2] ?></svg>
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
            <span class="portal-label">
                <img src="../assets/images/icons/layout-dashboard.svg" alt="">
                Attendance &amp; Payroll Portal
            </span>
            <span class="date-pill">
                <svg class="icon" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                <?= date('M j, Y') ?>
            </span>
        </div>

        <div class="content">
            <div class="page-head">
                <?php if ($eyebrow): ?><span class="eyebrow"><?= htmlspecialchars($eyebrow) ?></span><?php endif; ?>
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
                <?php if ($subtitle): ?><p><?= htmlspecialchars($subtitle) ?></p><?php endif; ?>
            </div>