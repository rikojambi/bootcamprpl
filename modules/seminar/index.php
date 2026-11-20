<?php
/** modules/seminar/index.php - Daftar seminar hasil PKL. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Seminar Hasil PKL & Bedah Kode';

if (can_manage()) {
    $rows = db_all('SELECT sp.*, s.name, s.class_name, s.nis,
        (SELECT COUNT(*) FROM seminar_assessments sa WHERE sa.student_id = s.id) AS assessed,
        (SELECT MAX(total_score) FROM seminar_assessments sa WHERE sa.student_id = s.id) AS last_score
        FROM seminar_profiles sp JOIN students s ON s.id = sp.student_id
        ORDER BY FIELD(sp.status, "siap", "dinilai", "draft"), s.name');
} else {
    $rows = db_all('SELECT sp.*, s.name, s.class_name, s.nis,
        (SELECT COUNT(*) FROM seminar_assessments sa WHERE sa.student_id = s.id) AS assessed,
        (SELECT MAX(total_score) FROM seminar_assessments sa WHERE sa.student_id = s.id) AS last_score
        FROM seminar_profiles sp JOIN students s ON s.id = sp.student_id
        WHERE s.id = ?', [(int) current_user()['student_id']]);
}
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Seminar Hasil PKL & Bedah Kode</h1>
        <p>Identitas siswa, presentasi hasil, bedah kode, developer challenge, dan lembar observasi asesor.</p>
    </div>
    <div class="btn-group">
        <a class="btn btn-outline" href="<?= url('ifp/seminar.php') ?>" target="_blank">🖥️ Tampilan IFP</a>
    </div>
</div>

<div class="grid grid-4" style="margin-bottom:20px">
    <div class="stat-card"><div class="stat-icon">🎤</div><div>
        <div class="stat-value"><?= count($rows) ?></div><div class="stat-label">Peserta Seminar</div></div></div>
    <div class="stat-card"><div class="stat-icon green">✅</div><div>
        <div class="stat-value"><?= count(array_filter($rows, fn($r) => $r['status'] === 'siap')) ?></div>
        <div class="stat-label">Siap Diseminarkan</div></div></div>
    <div class="stat-card"><div class="stat-icon orange">📝</div><div>
        <div class="stat-value"><?= count(array_filter($rows, fn($r) => (int) $r['assessed'] > 0)) ?></div>
        <div class="stat-label">Sudah Dinilai</div></div></div>
    <div class="stat-card"><div class="stat-icon red">❓</div><div>
        <div class="stat-value"><?= (int) db_value('SELECT COUNT(*) FROM question_bank') ?></div>
        <div class="stat-label">Bank Pertanyaan</div></div></div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Daftar Peserta</h2>
        <input type="text" id="table-filter" class="form-control" placeholder="🔍 Cari nama, kelas, atau judul aplikasi…"
               style="max-width:340px">
    </div>
    <div class="table-wrap">
        <table class="data-table" data-filterable>
            <thead><tr>
                <th>Siswa</th><th>Tempat PKL</th><th>Judul Aplikasi</th><th>Status</th><th>Nilai</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6" class="text-center muted">Belum ada data seminar.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e($r['name']) ?></strong><br><small><?= e($r['class_name']) ?> · <?= e($r['nis']) ?></small></td>
                    <td><?= e($r['pkl_place']) ?></td>
                    <td><?= e(str_limit($r['app_title'], 48)) ?></td>
                    <td><span class="badge badge-<?= ['draft'=>'gray','siap'=>'green','dinilai'=>'blue'][$r['status']] ?? 'gray' ?>">
                        <?= e(ucfirst($r['status'])) ?></span></td>
                    <td><?= $r['last_score'] !== null
                        ? '<span class="badge ' . score_badge_class((float) $r['last_score']) . '">' . fmt_score($r['last_score']) . '</span>'
                        : '<span class="muted">-</span>' ?></td>
                    <td class="actions">
                        <a class="btn btn-primary btn-sm" href="<?= url('modules/seminar/detail.php?id=' . (int) $r['student_id']) ?>">Buka</a>
                        <?php if (can_manage()): ?>
                        <a class="btn btn-light btn-sm" href="<?= url('modules/seminar/print.php?id=' . (int) $r['student_id']) ?>" target="_blank">Cetak</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
