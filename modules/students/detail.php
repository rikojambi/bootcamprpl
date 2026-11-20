<?php
/** modules/students/detail.php - Profil lengkap & perkembangan satu siswa. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$id = can_manage() ? (int) query('id', 0) : (int) current_user()['student_id'];
$student = db_one('SELECT * FROM students WHERE id = ?', [$id]);
if (!$student) redirect('modules/students/index.php');
$page_title = 'Profil — ' . $student['name'];

$courses = db_all('SELECT s.*, p.status AS progress_status, p.notes
    FROM bootcamp_sessions s
    LEFT JOIN student_progress p ON p.session_id = s.id AND p.student_id = ?
    ORDER BY s.session_no', [$id]);
$jobs = db_all('SELECT j.*, sj.status, sj.completed_at FROM jobsheets j
    LEFT JOIN student_jobsheets sj ON sj.jobsheet_id = j.id AND sj.student_id = ?
    ORDER BY j.sort_order', [$id]);
$assessments = db_all('SELECT * FROM competency_assessments WHERE student_id = ? ORDER BY id DESC', [$id]);
$attempts = db_all('SELECT ca.*, c.code, c.title FROM coding_attempts ca
    JOIN coding_challenges c ON c.id = ca.challenge_id
    WHERE ca.student_id = ? ORDER BY ca.id DESC LIMIT 10', [$id]);
$seminar = db_all('SELECT * FROM seminar_assessments WHERE student_id = ? ORDER BY id DESC', [$id]);
$remediation = db_all('SELECT * FROM remediation WHERE student_id = ? ORDER BY id DESC', [$id]);
$enrichment = db_all('SELECT * FROM enrichment WHERE student_id = ? ORDER BY id DESC', [$id]);

$doneCourses = count(array_filter($courses, fn($c) => $c['progress_status'] === 'selesai'));
$doneJobs = count(array_filter($jobs, fn($j) => $j['status'] === 'selesai'));
$latestScore = $assessments ? (float)$assessments[0]['total_score'] : null;
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <p style="margin:0 0 6px"><a href="<?= url('modules/students/index.php') ?>">← Monitoring Siswa</a></p>
        <h1><?= e($student['name']) ?></h1>
        <p><?= e($student['class_name']) ?> · NIS <?= e($student['nis']) ?> · <?= e(ucfirst($student['status'])) ?></p>
    </div>
    <div class="btn-group">
        <a class="btn btn-outline" href="<?= url('modules/assessment/detail.php?student_id=' . $id) ?>">🎯 Penilaian</a>
        <a class="btn btn-light" href="<?= url('modules/reports/index.php?student_id=' . $id) ?>">📊 Laporan</a>
    </div>
</div>

<div class="grid grid-4" style="margin-bottom:20px">
    <div class="stat-card"><div class="stat-icon">📘</div><div>
        <div class="stat-value"><?= $doneCourses ?>/<?= count($courses) ?></div><div class="stat-label">Materi Selesai</div></div></div>
    <div class="stat-card"><div class="stat-icon green">📋</div><div>
        <div class="stat-value"><?= $doneJobs ?>/<?= count($jobs) ?></div><div class="stat-label">Jobsheet Selesai</div></div></div>
    <div class="stat-card"><div class="stat-icon orange">⚡</div><div>
        <div class="stat-value"><?= count($attempts) ?></div><div class="stat-label">Attempt Live Coding</div></div></div>
    <div class="stat-card"><div class="stat-icon red">🎯</div><div>
        <div class="stat-value"><?= $latestScore !== null ? fmt_score($latestScore) : '-' ?></div><div class="stat-label">Nilai Kompetensi</div></div></div>
</div>

<div class="tabs">
    <button class="tab active" data-tab="profil">Profil & PKL</button>
    <button class="tab" data-tab="materi">Progres Materi</button>
    <button class="tab" data-tab="jobsheet">Progres Jobsheet</button>
    <button class="tab" data-tab="nilai">Penilaian</button>
    <button class="tab" data-tab="tindak">Tindak Lanjut</button>
</div>

<div class="tab-container">
<section class="tab-panel active" data-panel="profil">
    <div class="card">
        <h2>Data Siswa</h2>
        <div class="grid grid-2">
            <div><strong>NIS</strong><p><?= e($student['nis']) ?></p></div>
            <div><strong>Kelas</strong><p><?= e($student['class_name']) ?></p></div>
            <div><strong>Jenis Kelamin</strong><p><?= $student['gender'] === 'P' ? 'Perempuan' : 'Laki-laki' ?></p></div>
            <div><strong>Telepon</strong><p><?= e($student['phone'] ?: '-') ?></p></div>
            <div class="form-group full"><strong>Email</strong><p><?= e($student['email'] ?: '-') ?></p></div>
        </div>
        <h2>Data PKL</h2>
        <div class="grid grid-2">
            <div><strong>Tempat PKL</strong><p><?= e($student['pkl_place'] ?: '-') ?></p></div>
            <div><strong>Judul Aplikasi</strong><p><?= e($student['pkl_title'] ?: '-') ?></p></div>
            <div class="form-group full"><strong>Teknologi</strong><p><?= nl2br(e($student['technologies'] ?: '-')) ?></p></div>
            <div class="form-group full"><strong>Deskripsi Masalah</strong><p><?= nl2br(e($student['problem_desc'] ?: '-')) ?></p></div>
            <div class="form-group full"><strong>Fitur</strong><p><?= nl2br(e($student['features'] ?: '-')) ?></p></div>
        </div>
    </div>
</section>

<section class="tab-panel" data-panel="materi">
    <div class="card">
        <table class="data-table">
            <thead><tr><th>No</th><th>Judul Pertemuan</th><th>Status</th><th>Catatan</th></tr></thead>
            <tbody>
            <?php foreach ($courses as $c): ?>
                <tr>
                    <td><?= (int)$c['session_no'] ?></td>
                    <td><?= e(str_limit($c['title'], 70)) ?></td>
                    <td><span class="badge badge-<?= ['selesai'=>'green','sedang'=>'yellow','belum'=>'gray'][$c['progress_status'] ?? 'belum'] ?>"><?= e(ucfirst($c['progress_status'] ?? 'belum')) ?></span></td>
                    <td><?= e(str_limit($c['notes'], 60)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="tab-panel" data-panel="jobsheet">
    <div class="card">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Judul</th><th>Status</th><th>Selesai</th></tr></thead>
            <tbody>
            <?php foreach ($jobs as $j): ?>
                <tr>
                    <td><span class="badge badge-blue"><?= e($j['code']) ?></span></td>
                    <td><?= e(str_limit($j['title'], 60)) ?></td>
                    <td><span class="badge badge-<?= ['selesai'=>'green','proses'=>'yellow','belum'=>'gray'][$j['status'] ?? 'belum'] ?>"><?= e(ucfirst($j['status'] ?? 'belum')) ?></span></td>
                    <td><?= $j['completed_at'] ? e(format_date($j['completed_at'])) : '-' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="tab-panel" data-panel="nilai">
    <div class="card">
        <h3>Penilaian Kompetensi</h3>
        <?php if (!$assessments): ?><p class="muted">Belum ada penilaian.</p><?php endif; ?>
        <table class="data-table">
            <thead><tr><th>Tanggal</th><th>Nilai</th><th>Predikat</th></tr></thead>
            <tbody>
            <?php foreach ($assessments as $a): ?>
                <tr><td><?= e(format_date($a['assessment_date'])) ?></td>
                    <td><span class="badge <?= score_badge_class((float)$a['total_score']) ?>"><?= fmt_score($a['total_score']) ?></span></td>
                    <td><?= e(score_predicate((float)$a['total_score'])) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <h3 style="margin-top:20px">Penilaian Seminar</h3>
        <?php if (!$seminar): ?><p class="muted">Belum ada penilaian seminar.</p><?php endif; ?>
        <?php foreach ($seminar as $s): ?>
            <p><?= e(format_date($s['assessed_at'])) ?> — <span class="badge <?= score_badge_class((float)$s['total_score']) ?>"><?= fmt_score($s['total_score']) ?></span></p>
        <?php endforeach; ?>
        <h3 style="margin-top:20px">Live Coding Terbaru</h3>
        <?php if (!$attempts): ?><p class="muted">Belum ada attempt.</p><?php endif; ?>
        <?php foreach ($attempts as $a): ?>
            <p><?= e($a['code']) ?> — <?= e($a['title']) ?> · <?= e(ucfirst($a['status'])) ?>
                <?= $a['examiner_score'] !== null ? '· Nilai ' . (int)$a['examiner_score'] : '' ?></p>
        <?php endforeach; ?>
    </div>
</section>

<section class="tab-panel" data-panel="tindak">
    <div class="grid grid-2">
        <div class="card">
            <h3>Program Remediasi</h3>
            <?php if (!$remediation): ?><p class="muted">Belum ada program remediasi.</p><?php endif; ?>
            <?php foreach ($remediation as $r): ?>
                <div style="padding:10px 0;border-bottom:1px solid var(--line)">
                    <strong><?= e($r['competency']) ?></strong>
                    <span class="badge badge-<?= ['rencana'=>'gray','proses'=>'yellow','selesai'=>'green'][$r['status']] ?>"><?= e(ucfirst($r['status'])) ?></span>
                    <p style="font-size:.85rem"><?= nl2br(e($r['material'])) ?></p>
                    <p style="font-size:.85rem"><strong>Tugas:</strong> <?= nl2br(e($r['task'])) ?></p>
                    <?php if ($r['due_date']): ?><small class="muted">Batas: <?= e(format_date($r['due_date'])) ?></small><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card">
            <h3>Program Pengayaan</h3>
            <?php if (!$enrichment): ?><p class="muted">Belum ada program pengayaan.</p><?php endif; ?>
            <?php foreach ($enrichment as $r): ?>
                <div style="padding:10px 0;border-bottom:1px solid var(--line)">
                    <strong><?= e($r['competency']) ?></strong>
                    <span class="badge badge-<?= ['rencana'=>'gray','proses'=>'yellow','selesai'=>'green'][$r['status']] ?>"><?= e(ucfirst($r['status'])) ?></span>
                    <p style="font-size:.85rem"><?= nl2br(e($r['material'])) ?></p>
                    <p style="font-size:.85rem"><strong>Tugas:</strong> <?= nl2br(e($r['task'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
