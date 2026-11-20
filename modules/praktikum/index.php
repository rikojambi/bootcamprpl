<?php
/** modules/praktikum/index.php - Laboratorium praktikum interaktif (12 materi). */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Praktikum Fullstack — Laboratorium';

$labs = db_all('SELECT id, code, title, objective, duration_min, sort_order FROM jobsheets ORDER BY sort_order');
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Laboratorium Praktikum Fullstack</h1>
        <p>Proyek latihan: <strong>APLIKASI MANAJEMEN DATA SISWA</strong>. Setiap materi dilengkapi instruksi, source code, penjelasan bagian kode, dan tantangan modifikasi.</p>
    </div>
    <div class="btn-group">
        <a class="btn btn-primary" href="<?= url('modules/code/index.php') ?>">💻 Buka Editor Kode</a>
    </div>
</div>

<div class="card">
    <div class="card-head"><h2>12 Materi Praktikum</h2><span class="badge badge-blue"> Bertahap dari lingkungan hingga pengembangan mandiri</span></div>
    <div class="grid grid-3">
        <?php foreach ($labs as $lab): ?>
            <a class="card tight" style="margin:0;display:block;color:inherit" href="<?= url('modules/praktikum/detail.php?id=' . (int) $lab['id']) ?>">
                <span class="badge badge-blue"><?= e($lab['code']) ?></span>
                <h3 style="font-size:1.02rem;margin-top:10px"><?= e($lab['title']) ?></h3>
                <p class="muted" style="font-size:.83rem;margin:0"><?= e(str_limit($lab['objective'], 120)) ?></p>
                <small class="muted">⏱ <?= (int) $lab['duration_min'] ?> menit</small>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
