<?php
/** modules/challenges/result.php - Detail hasil satu attempt. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$id = (int) query('id', 0);

if (can_manage()) {
    $row = db_one('SELECT ca.*, s.name AS student_name, s.class_name, c.code AS challenge_code,
            c.title AS challenge_title, c.level, c.success_criteria, c.rubric
        FROM coding_attempts ca
        JOIN students s ON s.id = ca.student_id
        JOIN coding_challenges c ON c.id = ca.challenge_id
        WHERE ca.id = ?', [$id]);
} else {
    $row = db_one('SELECT ca.*, c.code AS challenge_code, c.title AS challenge_title,
            c.level, c.success_criteria, c.rubric
        FROM coding_attempts ca
        JOIN coding_challenges c ON c.id = ca.challenge_id
        WHERE ca.id = ? AND ca.student_id = ?', [$id, (int) current_user()['student_id']]);
}
if (!$row) redirect('modules/challenges/history.php');
$page_title = 'Hasil — ' . $row['challenge_code'];
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <p style="margin:0 0 6px"><a href="<?= url('modules/challenges/history.php') ?>">← Riwayat</a></p>
        <h1><?= e($row['challenge_code']) ?> — <?= e($row['challenge_title']) ?></h1>
        <?php if (can_manage()): ?><p>Siswa: <?= e($row['student_name']) ?> · <?= e($row['class_name']) ?></p><?php endif; ?>
    </div>
    <?php if (can_manage() && $row['status'] !== 'dinilai'): ?>
    <a class="btn btn-accent" href="<?= url('modules/challenges/grade.php?id=' . $id) ?>">✅ Beri Nilai</a>
    <?php endif; ?>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;align-items:start">
    <div class="card">
        <h2>Informasi Attempt</h2>
        <table class="data-table">
            <tr><th>Status</th><td><span class="badge badge-<?= ['berjalan'=>'yellow','selesai'=>'blue','dinilai'=>'green'][$row['status']] ?>"><?= e(ucfirst($row['status'])) ?></span></td></tr>
            <tr><th>Mulai</th><td><?= e(format_date($row['started_at'], true)) ?></td></tr>
            <tr><th>Selesai</th><td><?= $row['ended_at'] ? e(format_date($row['ended_at'], true)) : '-' ?></td></tr>
            <tr><th>Durasi</th><td><?= floor((int)$row['duration_sec']/60) ?> menit <?= (int)$row['duration_sec']%60 ?> detik</td></tr>
            <tr><th>Nilai</th><td><?= $row['examiner_score'] !== null ? (int)$row['examiner_score'] : 'Belum dinilai' ?></td></tr>
        </table>
        <h2 style="margin-top:18px">Catatan Penguji</h2>
        <p><?= nl2br(e($row['examiner_notes'] ?: '-')) ?></p>
        <h2>Kriteria Keberhasilan</h2>
        <p><?= nl2br(e($row['success_criteria'])) ?></p>
        <h2>Rubrik</h2>
        <p><?= nl2br(e($row['rubric'] ?: '-')) ?></p>
    </div>
    <div class="card">
        <h2>Hasil Kode Siswa</h2>
        <pre class="code-block" style="white-space:pre-wrap;max-height:600px"><?= e($row['result_text'] ?: '// Belum ada hasil.') ?></pre>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
