<?php
/** modules/jobsheet/detail.php - Detail jobsheet, checklist, dan penandaan selesai. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$id = (int) query('id', 1);
$job = db_one('SELECT * FROM jobsheets WHERE id = ?', [$id]);
if (!$job) redirect('modules/jobsheet/index.php');

$studentId = can_manage() ? (int) query('student_id', 0) : (int) current_user()['student_id'];
if (!$studentId) {
    $studentId = (int) db_value('SELECT MIN(id) FROM students');
}
$student = db_one('SELECT id, name, class_name FROM students WHERE id = ?', [$studentId]);
$progress = db_one('SELECT * FROM student_jobsheets WHERE student_id = ? AND jobsheet_id = ?', [$studentId, $id]);
$steps = array_filter(array_map('trim', preg_split('/\R/', (string) $job['steps']) ?: []));
$page_title = $job['code'] . ' — ' . $job['title'];
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <p style="margin:0 0 6px"><a href="<?= url('modules/jobsheet/index.php') ?>">← Daftar Jobsheet</a></p>
        <h1><?= e($job['code']) ?> — <?= e($job['title']) ?></h1>
        <p>Durasi <?= (int) $job['duration_min'] ?> menit · Siswa: <?= e($student['name'] ?? '-') ?></p>
    </div>
    <div class="btn-group">
        <a class="btn btn-outline" href="<?= url('modules/jobsheet/print.php?id=' . $id) ?>" target="_blank">🖨️ Cetak</a>
        <a class="btn btn-primary" href="<?= url('modules/code/index.php?source=jobsheet&id=' . $id . '&mode=lihat') ?>">💻 Lihat Kode di Editor</a>
    </div>
</div>

<div class="grid" style="grid-template-columns:1.25fr 1fr;align-items:start">
<div>
    <div class="card">
        <h2>Tujuan</h2><p><?= nl2br(e($job['objective'])) ?></p>
        <h2>Alat dan Bahan</h2><p><?= nl2br(e($job['tools'])) ?></p>
        <h2>Konsep</h2><p><?= nl2br(e($job['concept'])) ?></p>
    </div>

    <div class="card">
        <h2>Langkah Kerja</h2>
        <?php foreach ($steps as $i => $step): ?>
            <div class="form-check" style="align-items:flex-start;border-bottom:1px solid var(--line);padding:8px 0">
                <input type="checkbox" id="step<?= $i ?>">
                <label for="step<?= $i ?>" style="font-weight:500"><?= e(preg_replace('/^\d+\.\s*/', '', $step)) ?></label>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <h2>Source Code</h2>
        <pre class="code-block" style="white-space:pre-wrap;max-height:420px"><?= e($job['source_code'] ?: '// Tidak ada source code.') ?></pre>
    </div>
</div>

<div>
    <div class="card">
        <h2>Tugas</h2><p><?= nl2br(e($job['task'])) ?></p>
        <h2>Pertanyaan Refleksi</h2><p><?= nl2br(e($job['reflection'])) ?></p>
    </div>

    <div class="card">
        <div class="card-head"><h2>Checklist Penyelesaian</h2>
            <span class="badge badge-<?= ['belum'=>'gray','proses'=>'yellow','selesai'=>'green'][$progress['status'] ?? 'belum'] ?>">
                <?= e(ucfirst($progress['status'] ?? 'belum')) ?></span>
        </div>
        <ul style="padding-left:22px">
            <li>Membaca tujuan dan konsep</li>
            <li>Menyiapkan alat dan bahan</li>
            <li>Mengikuti langkah kerja</li>
            <li>Menjalankan source code di Laragon</li>
            <li>Menjawab pertanyaan refleksi</li>
        </ul>
        <div class="divider"></div>
        <form method="post" action="<?= url('modules/jobsheet/process.php') ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="jobsheet_id" value="<?= $id ?>">
            <input type="hidden" name="student_id" value="<?= $studentId ?>">
            <div class="form-group">
                <label>Status Penyelesaian</label>
                <select name="status">
                    <?php foreach (['belum'=>'Belum','proses'=>'Sedang Proses','selesai'=>'Selesai'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($progress['status'] ?? 'belum') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Catatan Siswa / Guru</label>
                <textarea name="notes" rows="3"><?= e($progress['notes'] ?? '') ?></textarea></div>
            <?php if ($studentId === (int) (current_user()['student_id'] ?? 0) || can_manage()): ?>
                <button class="btn btn-accent btn-block" type="submit">💾 Simpan Progres Jobsheet</button>
            <?php else: ?>
                <p class="muted">Mode guru: pilih siswa terlebih dahulu dari daftar jobsheet.</p>
            <?php endif; ?>
        </form>
    </div>
</div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
