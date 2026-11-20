<?php
/** index.php - Dashboard utama. */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth/auth.php';

require_login();
$user = current_user();
$page_title = 'Dashboard';

$totalStudents   = (int) db_value('SELECT COUNT(*) FROM students WHERE status = "aktif"');
$totalJobsheets  = (int) db_value('SELECT COUNT(*) FROM jobsheets');
$doneJobsheets   = (int) db_value('SELECT COUNT(*) FROM student_jobsheets WHERE status = "selesai"');
$progressPercent = ($totalStudents * $totalJobsheets) > 0
    ? round($doneJobsheets / ($totalStudents * $totalJobsheets) * 100, 1) : 0;

$avgCompetency = (float) db_value('SELECT AVG(total_score) FROM competency_assessments');
$readyCount = (int) db_value('SELECT COUNT(*) FROM (
        SELECT student_id, MAX(id) AS mid FROM competency_assessments GROUP BY student_id
    ) x JOIN competency_assessments c ON c.id = x.mid WHERE c.total_score >= 75');
$remedialCount = (int) db_value('SELECT COUNT(*) FROM (
        SELECT student_id, MAX(id) AS mid FROM competency_assessments GROUP BY student_id
    ) x JOIN competency_assessments c ON c.id = x.mid WHERE c.total_score < 75');

$weekData = db_all('SELECT week_no, COUNT(*) AS total,
        SUM(CASE WHEN p.status = "selesai" THEN 1 ELSE 0 END) AS done
    FROM bootcamp_sessions s
    LEFT JOIN student_progress p ON p.session_id = s.id AND p.student_id = ?
    GROUP BY week_no ORDER BY week_no', [(int) ($user['student_id'] ?? 0) ?: 1]);

$latestAssessments = db_all('SELECT c.*, s.name AS student_name FROM competency_assessments c
    JOIN students s ON s.id = c.student_id
    ORDER BY c.id DESC LIMIT 5');

$recentSessions = db_all('SELECT * FROM bootcamp_sessions ORDER BY session_no LIMIT 4');

$quickMenu = [
    ['modules/seminar/index.php',   '🎤', 'Seminar PKL',        'Presentasi, bedah kode, penilaian'],
    ['modules/materi/index.php',    '📘', 'Materi Bootcamp',    '16 pertemuan, 4 minggu'],
    ['modules/simulasi/index.php',  '🔄', 'Simulasi Arsitektur','Frontend → Backend → Database'],
    ['modules/praktikum/index.php', '🧪', 'Praktikum',          'Laboratorium + editor kode'],
    ['modules/jobsheet/index.php',  '📋', 'Jobsheet',           '12 lembar kerja praktikum'],
    ['modules/challenges/index.php','⚡', 'Live Coding',        '15 tantangan + timer'],
    ['modules/assessment/index.php','🎯', 'Penilaian',          '6 aspek kompetensi UKK'],
    ['modules/students/index.php',  '👥', 'Monitoring Siswa',   'Profil & perkembangan kelas'],
];
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Selamat datang, <?= e(explode(',', $user['full_name'])[0]) ?> 👋</h1>
        <p><?= e(SCHOOL_NAME) ?> · <?= e(PROGRAM_NAME) ?> · Tahun Ajaran <?= e(setting('academic_year')) ?></p>
    </div>
    <div class="btn-group">
        <a href="<?= url('ifp/index.php') ?>" target="_blank" class="btn btn-accent btn-lg">🖥️ Buka Mode Presentasi IFP</a>
    </div>
</div>

<div class="grid grid-4" style="margin-bottom:22px">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div><div class="stat-value"><?= $totalStudents ?></div><div class="stat-label">Jumlah Siswa Aktif</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">📋</div>
        <div><div class="stat-value"><?= fmt_score($progressPercent, 1) ?>%</div><div class="stat-label">Penyelesaian Jobsheet</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">🎯</div>
        <div><div class="stat-value"><?= fmt_score($avgCompetency) ?></div><div class="stat-label">Rata-rata Kompetensi</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">⚠️</div>
        <div><div class="stat-value"><?= $remedialCount ?></div><div class="stat-label">Perlu Remediasi</div></div>
    </div>
</div>

<div class="grid" style="grid-template-columns:2fr 1fr; align-items:start">
    <div>
        <div class="card">
            <div class="card-head"><h2>Akses Cepat Modul</h2><span class="badge badge-blue"><?= count($quickMenu) ?> modul</span></div>
            <div class="grid grid-4">
                <?php foreach ($quickMenu as [$link, $icon, $title, $desc]): ?>
                    <a class="ifp-tile" style="min-height:140px" href="<?= url($link) ?>">
                        <span class="tile-icon"><?= $icon ?></span>
                        <strong><?= e($title) ?></strong>
                        <small><?= e($desc) ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h2>Progres Materi per Minggu</h2>
                <a href="<?= url('modules/materi/index.php') ?>" class="btn btn-outline btn-sm">Lihat Materi</a>
            </div>
            <?php foreach ($weekData as $w):
                $pct = $w['total'] > 0 ? round(($w['done'] / $w['total']) * 100) : 0;
            ?>
                <div class="chart-row">
                    <strong>Minggu <?= (int) $w['week_no'] ?></strong>
                    <div class="progress"><span style="width:<?= $pct ?>%"></span></div>
                    <div class="chart-value"><?= (int) $w['done'] ?>/<?= (int) $w['total'] ?></div>
                </div>
            <?php endforeach; ?>
            <p class="muted" style="font-size:.8rem;margin-top:14px">Progres ditampilkan untuk akun siswa yang sedang login. Mode guru menampilkan ringkasan umum.</p>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-head"><h3>Ringkasan Kesiapan UKK</h3></div>
            <div class="text-center" style="padding:10px 0 18px">
                <div style="font-size:3.4rem;font-weight:900;color:var(--accent);line-height:1"><?= $readyCount ?></div>
                <div class="muted">siswa siap UKK</div>
            </div>
            <div class="progress green" style="margin-bottom:14px">
                <span style="width:<?= $totalStudents ? round($readyCount / $totalStudents * 100) : 0 ?>%"></span>
            </div>
            <div class="divider"></div>
            <p style="font-size:.85rem"><strong><?= $remedialCount ?></strong> siswa perlu remediasi berdasarkan nilai kompetensi terakhir.</p>
            <a href="<?= url('modules/reports/index.php') ?>" class="btn btn-primary btn-block">Lihat Tindak Lanjut</a>
        </div>

        <div class="card">
            <div class="card-head"><h3>Penilaian Terbaru</h3></div>
            <?php if (!$latestAssessments): ?>
                <p class="muted">Belum ada penilaian.</p>
            <?php else: ?>
                <?php foreach ($latestAssessments as $a): ?>
                    <div style="display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid var(--line);font-size:.85rem">
                        <span><?= e($a['student_name']) ?></span>
                        <span class="badge <?= score_badge_class((float) $a['total_score']) ?>"><?= fmt_score($a['total_score']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-head"><h3>Pertemuan Mendatang</h3></div>
            <?php foreach ($recentSessions as $s): ?>
                <div style="padding:8px 0;border-bottom:1px solid var(--line);font-size:.85rem">
                    <span class="badge badge-blue">P<?= (int) $s['session_no'] ?></span>
                    <?= e(str_limit($s['title'], 46)) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
