<?php
session_start();
require 'db.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['employee_id'] = $user['employee_id'];
        header("Location: " . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'employee/home.php'));
        exit;
    }
    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="/attendance/assets/images/logo.jpeg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Oppa's Samgyeopsal</title>
    <link rel="stylesheet" href="/attendance/assets/style.css">
    <style>
        /* Login page styles (split layout) */
        body.login-split { display: flex; min-height: 100vh; padding: 0; }

        .ls-logo-img { width: 56px; height: 56px; object-fit: contain; border-radius: 10px; background: #fff; padding: 4px; }
        .mobile-logo-img { display: none; width: 64px; height: 64px; object-fit: contain; margin: 0 auto 16px; }
        @media (max-width: 800px) { .mobile-logo-img { display: block; } }

        .ls-left {
            flex: 1.1;
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 48px 56px;
            color: #fff;
            background:
                radial-gradient(circle at 15% 15%, rgba(192, 57, 43, .45), transparent 55%),
                radial-gradient(circle at 90% 95%, rgba(192, 57, 43, .25), transparent 50%),
                var(--sidebar);
        }
        .ls-brand { display: flex; align-items: center; gap: 12px; }
        .ls-logo-img {
            width: 44px; height: 44px; border-radius: 10px;
        }
        .ls-brand strong { display: block; font-size: 16px; }
        .ls-brand span { display: block; font-size: 11px; letter-spacing: .08em; color: var(--sidebar-muted); margin-top: 2px; }

        .ls-hero .eyebrow { color: #F1948A; }
        .ls-hero h2 { font-size: 40px; line-height: 1.15; margin: 14px 0 16px; font-weight: 700; }
        .ls-hero p { color: var(--sidebar-text); font-size: 16px; max-width: 420px; margin: 0 0 28px; }
        .ls-hero ul { list-style: none; padding: 0; margin: 0; }
        .ls-hero li { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; font-size: 15px; color: #fff; }
        .ls-hero li::before {
            content: '✓';
            width: 24px; height: 24px; border-radius: 50%;
            background: rgba(192, 57, 43, .35); border: 1px solid var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
        }
        .ls-foot { font-size: 12px; color: var(--sidebar-muted); }

        .ls-right { flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px; background: var(--bg); }
        .ls-form {
            width: 100%; max-width: 400px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 40px 36px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
        }
        .ls-form .mobile-logo { display: none; }
        .ls-form h1 { font-size: 26px; margin-bottom: 6px; }
        .ls-form .sub { color: var(--muted); font-size: 14px; margin: 0 0 26px; }
        .ls-form input { width: 100%; margin-bottom: 18px; }
        .ls-form button { margin-top: 6px; height: 48px; }

        @media (max-width: 800px) {
            .ls-left { display: none; }
            .ls-form .mobile-logo {
                display: flex; width: 56px; height: 56px; margin: 0 auto 16px;
                border-radius: 50%; background: var(--primary); color: #fff;
                align-items: center; justify-content: center; font-size: 22px; font-weight: 700;
            }
            .ls-form { text-align: center; }
            .ls-form label { text-align: left; }
        }
    </style>
    
    
</head>
<body class="login-split">

    <div class="ls-left">
        <div class="ls-brand">
            <img src="/attendance/assets/images/logo.jpeg" alt="Oppa's Samgyeopsal logo" class="ls-logo-img">
            <div>
                <strong>Oppa's Samgyeopsal</strong>
                <span>ATTENDANCE &amp; PAYROLL</span>
            </div>
        </div>

        <div class="ls-hero">
            <span class="eyebrow">Biometric attendance system</span>
            <h2>Accurate time.<br>Fair pay.<br>In real time.</h2>
            <p>Track every clock in and clock out, and watch salaries update live.</p>
            <ul>
                <li>Fingerprint clock in and clock out</li>
                <li>Salary that updates in real time</li>
                <li>Transparent deductions with a full log</li>
            </ul>
        </div>

        <div class="ls-foot"> &middot; Biometric Attendance and Real-Time Salary Calculation System</div>
    </div>

    <div class="ls-right">
        <form method="POST" class="ls-form">
            <img src="/attendance/assets/images/logo.jpeg" alt="Logo" class="mobile-logo-img">
            <h1>Welcome back</h1>
            <p class="sub">Log in to manage attendance and payroll.</p>

            <?php if ($error) echo "<div class='msg error'>" . htmlspecialchars($error) . "</div>"; ?>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit" class="btn-block">Login</button>
        </form>
    </div>

</body>
</html>