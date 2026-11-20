<?php
/** modules/materi/index.php - Daftar 16 pertemuan bootcamp 4 minggu. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Materi Bootcamp 4 Minggu';

$studentId = (int) (current_user()['student_id'] ?? 0);
$sessions = db_all('SELECT s.*,
    (SELECT p.status FROM student_progress p WHERE p.session_id = s.id AND p.student_id = ?) AS my_status
    FROM bootcamp_sessions s ORDER BY s.session_no', [$studentId]);

$grouped = [];
foreach ($sessions as $s) $grouped[$s['week_no']][] = $s;

$weekTitles = [
    1 => 'Minggu 1 - Fondasi Fullstack',
    2 => 'Minggu 2 - Database dan CRUD',
    3 => 'Minggu 3 - Pengembangan Aplikasi',
    4 => 'Minggu 4 - Simulasi Industri',
];

$completed = count(array_filter($sessions, fn($s) => $s['my_status'] === 'selesai'));
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Materi Bootcamp — 16 Pertemuan</h1>
        <p>Materi disusun bertahap dari fondasi hingga simulasi industri. Bahasa sederhana, analogi dunia nyata, dan praktik langsung.</p>
    </div>
    <div class="btn-group">
        <a href="<?= url('ifp/materi.php?no=1') ?>" target="_blank" class="btn btn-accent btn-lg">🖥️ Mode Presentasi IFP</a>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Progres Keseluruhan</h2>
        <span class="badge badge-blue"><?= $completed ?> / <?= count($sessions) ?> pertemuan selesai</span>
    </div>
    <div class="progress green"><span style="width:<?= count($sessions) ? round($completed/count($sessions)*100) : 0 ?>%"></span></div>
</div>

<?php foreach ($grouped as $week => $items): ?>
    <h2 style="margin-top:28px"><?= e($weekTitles[$week] ?? ('Minggu ' . $week)) ?></h2>
    <div class="grid grid-2">
        <?php foreach ($items as $s): ?>
            <a class="card" style="margin:0;display:block;color:inherit"
               href="<?= url('modules/materi/detail.php?no=' . (int) $s['session_no']) ?>">
                <div class="card-head" style="margin-bottom:8px">
                    <span class="badge badge-blue">Pertemuan <?= (int) $s['session_no'] ?></span>
                    <?php if ($s['my_status'] === 'selesai'): ?>
                        <span class="badge badge-green">✓ Selesai</span>
                    <?php elseif ($s['my_status'] === 'sedang'): ?>
                        <span class="badge badge-yellow">Sedang dipelajari</span>
                    <?php else: ?>
                        <span class="badge badge-gray">Belum</span>
                    <?php endif; ?>
                </div>
                <h3 style="font-size:1.05rem"><?= e($s['title']) ?></h3>
                <p class="muted" style="font-size:.85rem;margin:0"><?= e(str_limit($s['objectives'], 150)) ?></p>
                <small class="muted">⏱ <?= (int) $s['duration_min'] ?> menit</small>
            </a>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
