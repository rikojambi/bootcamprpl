<?php
/**
 * login.php - Halaman login session authentication.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth/auth.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = '';

if (is_post()) {
    csrf_guard();
    [$ok, $message] = attempt_login(post('username'), post('password'));
    if ($ok) {
        flash('success', $message);
        db_run('INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)',
            [(int) $_SESSION['user_id'], 'login', 'Login berhasil', client_ip()]);
        redirect('index.php');
    }
    $error = $message;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — <?= e(APP_SHORT) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        body { background: linear-gradient(135deg, #10234a, #1552b8); min-height: 100vh;
               display: grid; place-items: center; padding: 20px; }
        .login-card { width: 100%; max-width: 470px; background: #fff;
                      border-radius: 20px; padding: 36px; box-shadow: 0 18px 50px rgba(0,0,0,.28); }
        .login-logo { width: 64px; height: 64px; border-radius: 16px; margin: 0 auto 18px;
                      background: linear-gradient(135deg,#2d7ff9,#00c2a2); color:#fff;
                      display:grid; place-items:center; font-weight:900; font-size:1.6rem; }
        .login-card h1 { text-align:center; font-size:1.35rem; }
        .login-card .sub { text-align:center; color:var(--muted); font-size:.85rem; margin-bottom:24px; }
        .credential-note { background:var(--primary-light); border-radius:10px; padding:12px 16px;
                           font-size:.82rem; color:var(--primary-dark); margin-top:18px; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">BF</div>
    <h1>BOOTCAMP PRE-UKK<br>FULLSTACK DEVELOPER</h1>
    <p class="sub"><?= e(SCHOOL_NAME) ?> · <?= e(PROGRAM_NAME) ?><br>Mode Lokal Offline IFP Edition</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <?php render_flash(); ?>

    <form method="post" action="login.php">
        <?php csrf_field(); ?>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= old('username') ?>"
                   autocomplete="username" required autofocus placeholder="Contoh: guru atau 2023001">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   autocomplete="current-password" required placeholder="Masukkan password">
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">🔐 Masuk Aplikasi</button>
    </form>

    <div class="credential-note">
        <strong>Akun demo:</strong><br>
        Guru &nbsp;: <code>guru</code> / <code>guru123</code><br>
        Siswa : <code>2023001</code> / <code>siswa123</code>
    </div>
</div>
</body>
</html>
