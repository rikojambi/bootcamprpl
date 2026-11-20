<?php
/** modules/assessment/index.php - Daftar penilaian kompetensi siswa. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Penilaian Kompetensi';

$user = current_user();
$studentId = (int) ($user['student_id'] ?? 0);

if (can_manage()) {
    $rows = db_all('SELECT s.*,
            c.id AS assessment_id, c.total_score, c.assessment_date,
            (SELECT COUNT(*) FROM competency_assessments c2 WHERE c2.student_id = s.id) AS assessment_count
        FROM students s
        LEFT JOIN competency_assessments c ON c.id = (
            SELECT MAX(id) FROM competency_assessments WHERE student_id = s.id
        )
        WHERE s.status = "aktif"
        ORDER BY s.class_name, s.name');
} else {
    $rows = db_all('SELECT s.*,
            c.id AS assessment_id, c.total_score, c.assessment_date,
            (SELECT COUNT(*) FROM competency_assessments c2 WHERE c2.student_id = s.id) AS assessment_count
        FROM students s
        LEFT JOIN competency_assessments c ON c.id = (
            SELECT MAX(id) FROM competency_assessments WHERE student_id = s.id
        )
        WHERE s.id = ?', [$studentId]);
}

$scored = array_filter($rows, fn($r) => $r['total_score'] !== null);
$avg = $scored ? array_sum(array_column($scored, 'total_score')) / count($scored) : 0;
$remedial = count(array_filter($scored, fn($r) => (float)$r['total_score'] < 75));
$enrich = count(array_filter($scored, fn($r) => (float)$r['total_score'] >= 85));
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Penilaian Kompetensi UKK</h1>
        <p>6 aspek kompetensi dengan bobot sesuai skema penilaian. Skala 1–4 untuk tiap aspek.</p>
    </div>
    <div class="btn-group">
        <a class="btn btn-outline" href="<?= url('modules/reports/index.php') ?>">📊 Laporan Tindak Lanjut</a>
    </div>
</div>

<div class="grid grid-4" style="margin-bottom:20px">
    <div class="stat-card"><div class="stat-icon">🎯</div><div>
        <div class="stat-value"><?= fmt_score($avg) ?></div><div class="stat-label">Rata-rata Kompetensi</div></div></div>
    <div class="stat-card"><div class="stat-icon green">✅</div><div>
        <div class="stat-value"><?= count($scored) ?></div><div class="stat-label">Sudah Dinilai</div></div></div>
    <div class="stat-card"><div class="stat-icon red">⚠️</div><div>
        <div class="stat-value"><?= $remedial ?></div><div class="stat-label">Perlu Remediasi</div></div></div>
    <div class="stat-card"><div class="stat-icon orange">🚀</div><div>
        <div class="stat-value"><?= $enrich ?></div><div class="stat-label">Siap Pengayaan</div></div></div>
</div>

<div class="card">
    <div class="card-head"><h2>Daftar Siswa</h2>
        <input type="text" id="table-filter" placeholder="🔍 Cari nama atau kelas…" style="max-width:320px">
    </div>
    <div class="table-wrap">
        <table class="data-table" data-filterable>
            <thead><tr>
                <th>Siswa</th><th>Kelas</th><th>Nilai Terakhir</th><th>Predikat</th><th>Tanggal</th><th>Jumlah Penilaian</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e($r['name']) ?></strong><br><small>NIS <?= e($r['nis']) ?></small></td>
                    <td><?= e($r['class_name']) ?></td>
                    <td><?= $r['total_score'] !== null
                        ? '<span class="badge ' . score_badge_class((float)$r['total_score']) . '">' . fmt_score($r['total_score']) . '</span>'
                        : '<span class="muted">Belum dinilai</span>' ?></td>
                    <td><?= $r['total_score'] !== null ? e(score_predicate((float)$r['total_score'])) : '-' ?></td>
                    <td><?= $r['assessment_date'] ? e(format_date($r['assessment_date'])) : '-' ?></td>
                    <td><?= (int)$r['assessment_count'] ?> kali</td>
                    <td class="actions">
                        <a class="btn btn-primary btn-sm" href="<?= url('modules/assessment/detail.php?student_id=' . (int)$r['id']) ?>">
                            <?= can_manage() ? 'Nilai' : 'Lihat' ?></a>
                        <?php if ($r['assessment_id']): ?>
                        <a class="btn btn-light btn-sm" href="<?= url('modules/assessment/print.php?student_id=' . (int)$r['id']) ?>" target="_blank">Cetak</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3>Bobot Penilaian</h3>
    <div class="grid grid-3">
        <?php foreach (COMPETENCY_LABELS as $key => $label): ?>
            <div style="padding:10px 0;border-bottom:1px solid var(--line)">
                <strong><?= e($label) ?></strong> <span class="badge badge-blue"><?= COMPETENCY_WEIGHTS[$key] ?>%</span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
