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
        body.login-split { display: flex; min-height: 100vh; background: var(--bg); }

        .ls-left {
            position: relative;
            flex: 0 0 42%;
            max-width: 610px;
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 44px 48px 48px;
            color: #fff;
            background: var(--sidebar) url('/attendance/assets/images/login-bg.jpg') center / cover no-repeat;
            overflow: hidden;
        }
        .ls-left::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(23, 20, 17, .62) 0%, rgba(23, 20, 17, .44) 52%, rgba(23, 20, 17, .96) 100%);
        }
        .ls-left > * { position: relative; }

        .ls-hero { display: flex; flex-direction: column; gap: 22px; }
        .ls-status { display: inline-flex; align-items: center; gap: 8px; font-size: 10px; text-transform: uppercase; color: #DAD2CA; }
        .ls-status::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--success); }
        .ls-hero h2 { font-size: 46px; line-height: 1.08; font-weight: 400; margin: 0; letter-spacing: -.01em; }
        .ls-hero p { color: #D4CCC5; font-size: 15px; line-height: 1.55; max-width: 430px; margin: 0; }
        .ls-hero ul { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 10px 24px; }
        .ls-hero li { display: flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 600; color: #fff; }
        .ls-hero li img { width: 15px; height: 15px; }

        .ls-brand { display: flex; align-items: center; gap: 12px; }
        .ls-brand .brand-mark { width: 44px; height: 44px; font-size: 18px; }
        .ls-brand strong { display: block; font-size: 15px; font-weight: 400; }
        .ls-brand div span { display: block; font-size: 10px; text-transform: uppercase; color: #BDB5AE; margin-top: 2px; }

        .ls-right {
            flex: 1;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 40px;
            padding: 48px 32px;
        }
        .ls-form {
            width: 100%; max-width: 452px;
            display: flex; flex-direction: column; gap: 24px;
            background: var(--sidebar);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 36px;
            box-shadow: 0 18px 48px rgba(0, 0, 0, .18);
            color: #fff;
        }
        .ls-form .mobile-brand { display: none; }
        .ls-heading { display: flex; flex-direction: column; gap: 10px; }
        .ls-heading .eyebrow { color: #fff; font-size: 10px; }
        .ls-form h1 { font-size: 30px; line-height: 1.15; }
        .ls-form .sub { font-size: 13px; line-height: 1.5; margin: 0; }
        .ls-fields { display: flex; flex-direction: column; gap: 18px; }
        .ls-form label { color: #fff; font-size: 13px; margin-bottom: 8px; }
        .ls-input {
            display: flex; align-items: center; gap: 12px;
            height: 54px; padding: 0 16px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-input);
        }
        .ls-input:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(192, 57, 43, .25); }
        .ls-input img { width: 18px; height: 18px; flex-shrink: 0; }
        .ls-input input { flex: 1; min-width: 0; height: 100%; padding: 0; border: 0; background: transparent; font-size: 13px; }
        .ls-input input:focus { box-shadow: none; }
        .ls-input input::placeholder { color: var(--faint); }
        .ls-form button { height: 54px; gap: 10px; font-size: 15px; font-weight: 400; border-radius: var(--radius-input); }
        .ls-form button img { width: 17px; height: 17px; }
        .ls-form .msg { margin: 0; }
        .ls-foot { font-size: 10.5px; color: var(--faint); text-align: center; }

        @media (max-width: 900px) {
            .ls-left { display: none; }
            .ls-form .mobile-brand { display: flex; align-items: center; gap: 10px; }
            .ls-form .mobile-brand strong { font-size: 14px; font-weight: 500; }
        }
    </style>
</head>
<body class="login-split">

    <div class="ls-left">
        <div class="ls-hero">
            <span class="ls-status">Biometric attendance system</span>
            <h2>Every shift counts. Every minute stays transparent.</h2>
            <p>A secure biometric attendance workspace built for the people who keep Oppa&rsquo;s Samgyeopsal moving.</p>
            <ul>
                <li><img src="/attendance/assets/images/icons/fingerprint.svg" alt="">Biometric verified</li>
                <li><img src="/attendance/assets/images/icons/timer.svg" alt="">Real-time salary</li>
                <li><img src="/attendance/assets/images/icons/shield-check.svg" alt="">Transparent deductions</li>
            </ul>
        </div>

        <div class="ls-brand">
            <span class="brand-mark">OS</span>
            <div>
                <strong>Oppa's Samgyeopsal</strong>
                <span>Attendance &amp; Payroll</span>
            </div>
        </div>
    </div>

    <div class="ls-right">
        <form method="POST" class="ls-form">
            <div class="mobile-brand"><span class="brand-mark">OS</span><strong>Oppa's Samgyeopsal</strong></div>

            <div class="ls-heading">
                <span class="eyebrow">Welcome back</span>
                <h1>Sign in to your account</h1>
                <p class="sub">Access attendance, live salary totals and authorized management tools.</p>
            </div>

            <?php if ($error) echo "<div class='msg error'>" . htmlspecialchars($error) . "</div>"; ?>

            <div class="ls-fields">
                <div>
                    <label for="username">Username</label>
                    <div class="ls-input">
                        <img src="/attendance/assets/images/icons/user-round.svg" alt="">
                        <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
                    </div>
                </div>
                <div>
                    <label for="password">Password</label>
                    <div class="ls-input">
                        <img src="/attendance/assets/images/icons/lock-keyhole.svg" alt="">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-block">Sign in <img src="/attendance/assets/images/icons/arrow-right.svg" alt=""></button>
        </form>

        <div class="ls-foot">Biometric Attendance and Real-Time Salary Calculation System</div>
    </div>

</body>
</html>