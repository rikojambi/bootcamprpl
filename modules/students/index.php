<?php
/** modules/students/index.php - Monitoring seluruh siswa. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Monitoring Siswa';

// Siswa hanya melihat data dirinya sendiri; guru/admin melihat semua siswa.
$ownStudentId = can_manage() ? 0 : (int) current_user()['student_id'];
$rows = db_all('SELECT s.*,
        (SELECT MAX(total_score) FROM competency_assessments c WHERE c.student_id = s.id) AS last_score,
        (SELECT COUNT(*) FROM student_jobsheets sj WHERE sj.student_id = s.id AND sj.status = "selesai") AS jobs_done,
        (SELECT COUNT(*) FROM student_progress sp WHERE sp.student_id = s.id AND sp.status = "selesai") AS materials_done,
        (SELECT COUNT(*) FROM coding_attempts ca WHERE ca.student_id = s.id) AS coding_attempts,
        (SELECT COUNT(*) FROM seminar_assessments sa WHERE sa.student_id = s.id) AS seminar_count
    FROM students s
    WHERE s.status = "aktif" AND (? = 0 OR s.id = ?)
    ORDER BY s.class_name, s.name', [$ownStudentId, $ownStudentId]);

$totalJobs = (int) db_value('SELECT COUNT(*) FROM jobsheets');
$totalSessions = (int) db_value('SELECT COUNT(*) FROM bootcamp_sessions');
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1><?= can_manage() ? 'Monitoring Siswa' : 'Progres Belajar Saya' ?></h1>
        <p><?= can_manage()
            ? 'Pantau perkembangan belajar, penyelesaian jobsheet, progres materi, dan nilai kompetensi setiap siswa.'
            : 'Lihat perkembangan belajar, penyelesaian jobsheet, progres materi, dan nilai kompetensi Anda.' ?></p>
    </div>
    <?php if (can_manage()): ?>
    <div class="btn-group">
        <a class="btn btn-outline" href="<?= url('modules/reports/export.php') ?>">⬇️ Ekspor CSV</a>
        <a class="btn btn-primary" href="<?= url('modules/reports/import.php') ?>">⬆️ Impor CSV</a>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-head"><h2>Daftar Siswa (<?= count($rows) ?>)</h2>
        <input type="text" id="table-filter" placeholder="🔍 Cari nama, NIS, atau kelas…" style="max-width:320px">
    </div>
    <div class="table-wrap">
        <table class="data-table" data-filterable>
            <thead><tr>
                <th>Siswa</th><th>Kelas</th><th>Jobsheet</th><th>Materi</th><th>Live Coding</th><th>Nilai Kompetensi</th><th>Status</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <?php
                $score = $r['last_score'] !== null ? (float)$r['last_score'] : null;
                $jobPct = $totalJobs ? round((int)$r['jobs_done'] / $totalJobs * 100) : 0;
                $matPct = $totalSessions ? round((int)$r['materials_done'] / $totalSessions * 100) : 0;
                ?>
                <tr>
                    <td><strong><?= e($r['name']) ?></strong><br><small>NIS <?= e($r['nis']) ?></small></td>
                    <td><?= e($r['class_name']) ?></td>
                    <td>
                        <div class="progress" style="min-width:80px"><span style="width:<?= $jobPct ?>%"></span></div>
                        <small class="muted"><?= (int)$r['jobs_done'] ?>/<?= $totalJobs ?></small>
                    </td>
                    <td>
                        <div class="progress green" style="min-width:80px"><span style="width:<?= $matPct ?>%"></span></div>
                        <small class="muted"><?= (int)$r['materials_done'] ?>/<?= $totalSessions ?></small>
                    </td>
                    <td><?= (int)$r['coding_attempts'] ?> attempt</td>
                    <td><?= $score !== null
                        ? '<span class="badge ' . score_badge_class($score) . '">' . fmt_score($score) . '</span>'
                        : '<span class="muted">Belum dinilai</span>' ?></td>
                    <td>
                        <?php if ($score === null): ?>
                            <span class="badge badge-gray">Belum dinilai</span>
                        <?php elseif ($score < 75): ?>
                            <span class="badge badge-red">Remediasi</span>
                        <?php elseif ($score >= 85): ?>
                            <span class="badge badge-green">Pengayaan</span>
                        <?php else: ?>
                            <span class="badge badge-blue">Kompeten</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions"><a class="btn btn-primary btn-sm" href="<?= url('modules/students/detail.php?id=' . (int)$r['id']) ?>">Detail</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
