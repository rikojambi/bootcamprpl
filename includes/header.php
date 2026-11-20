<?php
/**
 * header.php - Kerangka tampilan_mode="guru" (header + sidebar).
 * Dipanggil setelah require_login()/require_manager() di tiap halaman.
 */
if (!isset($page_title)) {
    $page_title = APP_SHORT;
}

$user = current_user();
$currentPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

// Tentukan menu aktif berdasarkan potongan URL
$activeKey = '';
$map = [
    'dashboard'    => ['index.php'],
    'seminar'      => ['/modules/seminar/'],
    'materi'       => ['/modules/materi/'],
    'praktikum'    => ['/modules/praktikum/', '/modules/code/'],
    'jobsheet'     => ['/modules/jobsheet/'],
    'challenges'   => ['/modules/challenges/'],
    'questions'    => ['/modules/questions/'],
    'assessment'   => ['/modules/assessment/'],
    'students'     => ['/modules/students/'],
    'reports'      => ['/modules/reports/'],
    'settings'     => ['/modules/settings/'],
    'simulasi'     => ['/modules/simulasi/'],
];
foreach ($map as $key => $patterns) {
    foreach ($patterns as $pattern) {
        if ($key === 'dashboard') {
            $base = rtrim(BASE_PATH, '/');
            if (rtrim($currentPath, '/') === $base . '/index.php' || rtrim($currentPath, '/') === $base) {
                $activeKey = 'dashboard';
            }
        } elseif (strpos($currentPath, $pattern) !== false) {
            $activeKey = $key;
        }
    }
}

$menu = [
    ['dashboard',  '▦', 'Dashboard',            'index.php'],
    ['seminar',    '🎤', 'Seminar Hasil PKL',   'modules/seminar/index.php'],
    ['materi',     '📘', 'Materi Bootcamp',     'modules/materi/index.php'],
    ['simulasi',   '🔄', 'Simulasi Arsitektur', 'modules/simulasi/index.php'],
    ['praktikum',  '🧪', 'Praktikum Fullstack', 'modules/praktikum/index.php'],
    ['jobsheet',   '📋', 'Jobsheet',            'modules/jobsheet/index.php'],
    ['challenges', '⚡', 'Tantangan Live Coding','modules/challenges/index.php'],
    ['questions',  '❓', 'Bank Soal',           'modules/questions/index.php'],
    ['assessment', '🎯', 'Penilaian Kompetensi','modules/assessment/index.php'],
    ['students',   '👥', 'Monitoring Siswa',    'modules/students/index.php'],
    ['reports',    '📊', 'Laporan & Tindak Lanjut','modules/reports/index.php'],
    ['settings',   '⚙️', 'Pengaturan',          'modules/settings/index.php'],
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title><?= e($page_title) ?> — <?= e(APP_SHORT) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="logo">
                <div class="logo-mark">BF</div>
                <div>
                    <strong>BOOTCAMP PRE-UKK<br>FULLSTACK DEVELOPER</strong>
                    <small><?= e(SCHOOL_NAME) ?> · LOCAL IFP</small>
                </div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Menu Utama</div>
            <?php foreach ($menu as $item): ?>
                <?php
                [$key, $icon, $label, $link] = $item;
                if ($key === 'settings' && !can_manage()) { continue; }
                if (!can_manage() && $key === 'students') $label = 'Progres Belajar Saya';
                if (!can_manage() && $key === 'reports') $label = 'Rekomendasi Belajar';
                ?>
                <a class="nav-item <?= $activeKey === $key ? 'active' : '' ?>"
                   href="<?= url($link) ?>">
                    <span class="ico"><?= $icon ?></span>
                    <span><?= e($label) ?></span>
                    <?php if ($activeKey === $key): ?><span class="chev">›</span><?php endif; ?>
                </a>
            <?php endforeach; ?>
            <div class="nav-label">Tampilan</div>
            <a class="nav-item" href="<?= url('ifp/index.php') ?>" target="_blank">
                <span class="ico">🖥️</span><span>Mode Presentasi IFP</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            v<?= APP_VERSION ?> · <?= e(AUTHOR_NAME) ?><br>
            Berjalan offline di Laragon
        </div>
    </aside>

    <div class="main-area">
        <header class="topbar">
            <h1><?= e($page_title) ?></h1>
            <div class="topbar-actions">
                <button type="button" class="btn btn-light btn-sm no-print" onclick="changeFontSize(-1)" title="Perkecil teks">A−</button>
                <button type="button" class="btn btn-light btn-sm no-print" onclick="changeFontSize(1)" title="Perbesar teks">A＋</button>
                <button type="button" class="btn btn-outline btn-sm no-print" onclick="toggleFullscreen()" title="Layar penuh">⛶ Fullscreen</button>
                <?php if ($user): ?>
                    <div class="user-chip">
                        <span class="avatar"><?= e(mb_substr($user['full_name'], 0, 1)) ?></span>
                        <span>
                            <?= e($user['full_name']) ?><br>
                            <small><?= e(role_label($user['role'])) ?></small>
                        </span>
                    </div>
                    <a class="btn btn-light btn-sm no-print" href="<?= url('logout.php') ?>">Keluar</a>
                <?php endif; ?>
            </div>
        </header>
        <main class="content">
        <?php render_flash(); ?>
