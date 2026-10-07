<?php
/*
 * Shared helper functions used across the system.
 * (User-defined functions with parameters, default argument values and return values.)
 */

// Convert seconds to hours, rounded to $decimals places (default 2)
function secondsToHours($secs, $decimals = 2) {
    return round($secs / 3600, $decimals);
}

// Salary earned for $secs seconds at $rate pesos per hour, rounded to centavos by default
function computePay($secs, $rate, $decimals = 2) {
    $hours = $secs / 3600;
    return round($hours * $rate, $decimals);
}

// Format a number as pesos, e.g. 1234.5 -> ₱1,234.50 and -20 -> -₱20.00
function peso($n) {
    return ($n < 0 ? '-₱' : '₱') . number_format(abs($n), 2);
}

// Initials from a name: first letter of the first and last word, e.g. "Paul Tenorio" -> "PT"
function initials($name) {
    $parts = preg_split('/\s+/', trim($name));
    $count = count($parts);
    $result = '';
    for ($i = 0; $i < $count; $i++) {
        if ($i == 0 || ($i == $count - 1 && $count > 1)) {
            $result .= strtoupper(substr($parts[$i], 0, 1));
        }
    }
    return $result !== '' ? $result : '?';
}

// Save an entry in the audit log
function logAction($pdo, $uid, $action, $details) {
    $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)")
        ->execute([$uid, $action, $details]);
}
