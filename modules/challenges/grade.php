<?php
/** modules/challenges/grade.php - Form penilaian attempt oleh guru. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();
$id = (int) query('id', 0);
$row = db_one('SELECT ca.*, s.name AS student_name, c.code AS challenge_code, c.title AS challenge_title, c.rubric
    FROM coding_attempts ca
    JOIN students s ON s.id = ca.student_id
    JOIN coding_challenges c ON c.id = ca.challenge_id
    WHERE ca.id = ?', [$id]);
if (!$row) redirect('modules/challenges/history.php');
$page_title = 'Nilai — ' . $row['challenge_code'];
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <p style="margin:0 0 6px"><a href="<?= url('modules/challenges/result.php?id=' . $id) ?>">← Detail Hasil</a></p>
        <h1>Penilaian Live Coding</h1>
        <p><?= e($row['student_name']) ?> · <?= e($row['challenge_code']) ?> — <?= e($row['challenge_title']) ?></p>
    </div>
</div>

<div class="grid" style="grid-template-columns:1fr 1.2fr;align-items:start">
    <div class="card">
        <h2>Rubrik</h2>
        <p><?= nl2br(e($row['rubric'] ?: 'Ketepatan, kerapian, kecepatan, dan penjelasan.')) ?></p>
        <form method="post" action="<?= url('modules/challenges/process.php') ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="grade">
            <input type="hidden" name="attempt_id" value="<?= $id ?>">
            <div class="form-group">
                <label>Nilai (0-100)</label>
                <input type="number" name="score" min="0" max="100" value="<?= (int)($row['examiner_score'] ?? 0) ?>" required>
            </div>
            <div class="form-group">
                <label>Catatan Penguji</label>
                <textarea name="notes" rows="5"><?= e($row['examiner_notes'] ?? '') ?></textarea>
            </div>
            <button class="btn btn-accent btn-block btn-lg" type="submit">💾 Simpan Penilaian</button>
        </form>
    </div>
    <div class="card">
        <h2>Hasil Kode Siswa</h2>
        <pre class="code-block" style="white-space:pre-wrap;max-height:600px"><?= e($row['result_text'] ?: '// Belum ada hasil.') ?></pre>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
