<?php
session_start();

// Relative URL to a file in the app root, so the app works from any folder name.
function appUrl($path) {
    $rel = str_replace('\\', '/', substr(realpath(dirname($_SERVER['SCRIPT_FILENAME'])), strlen(__DIR__)));
    $rel = trim($rel, '/');
    return str_repeat('../', $rel === '' ? 0 : substr_count($rel, '/') + 1) . $path;
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . appUrl("login.php"));
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit("Forbidden");
    }
}