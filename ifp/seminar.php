<?php
/** ifp/seminar.php - Tampilan IFP untuk seminar hasil PKL. */
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/auth/auth.php';
require_once __DIR__ . '/_layout.php';

require_login();

$rows = db_all('SELECT sp.*, s.name, s.class_name, s.nis,
        (SELECT MAX(total_score) FROM seminar_assessments sa WHERE sa.student_id = s.id) AS last_score
    FROM seminar_profiles sp JOIN students s ON s.id = sp.student_id
    ORDER BY FIELD(sp.status, "siap", "dinilai", "draft"), s.name');

$studentId = (int) query('student_id', 0);
$detail = $studentId ? db_one('SELECT sp.*, s.name, s.class_name, s.nis, s.pkl_place AS s_pkl, s.pkl_title
    FROM seminar_profiles sp JOIN students s ON s.id = sp.student_id WHERE s.id = ?', [$studentId]) : null;
$assessment = $studentId ? db_one('SELECT * FROM seminar_assessments WHERE student_id = ? ORDER BY id DESC LIMIT 1', [$studentId]) : null;

ifp_header('SEMINAR HASIL PKL', 'Presentasi · Bedah Kode · Penilaian Asesor');
?>

<main class="ifp-body wide">
    <?php if ($detail): ?>
        <div class="ifp-slide" id="ifp-slide">
            <a class="ifp-btn small" href="<?= url('ifp/seminar.php') ?>">← Daftar Peserta</a>
            <h2 style="margin-top:18px"><?= e($detail['name']) ?></h2>
            <p><span class="ifp-badge"><?= e($detail['class_name']) ?></span> NIS <?= e($detail['nis']) ?></p>

            <div class="ifp-grid cols-2">
                <div>
                    <h3>Identitas PKL</h3>
                    <table class="data-table">
                        <tr><th>Tempat PKL</th><td><?= e($detail['pkl_place']) ?></td></tr>
                        <tr><th>Periode</th><td><?= e($detail['pkl_period'] ?: '-') ?></td></tr>
                        <tr><th>Judul Aplikasi</th><td><?= e($detail['app_title']) ?></td></tr>
                        <tr><th>Teknologi</th><td><?= nl2br(e($detail['technologies'])) ?></td></tr>
                        <tr><th>Status</th><td><span class="ifp-badge <?= $detail['status'] === 'siap' ? 'green' : '' ?>"><?= e(ucfirst($detail['status'])) ?></span></td></tr>
                    </table>
                    <h3>Deskripsi Masalah</h3>
                    <p><?= nl2br(e($detail['problem_desc'] ?: '-')) ?></p>
                    <h3>Fitur Aplikasi</h3>
                    <p><?= nl2br(e($detail['features'] ?: '-')) ?></p>
                </div>
                <div>
                    <h3>Presentasi</h3>
                    <p><strong>Analisis Kebutuhan:</strong><br><?= nl2br(e($detail['requirement'] ?: '-')) ?></p>
                    <p><strong>Arsitektur:</strong><br><?= nl2br(e($detail['architecture'] ?: '-')) ?></p>
                    <p><strong>ERD / Database:</strong><br><?= nl2br(e($detail['erd'] ?: '-')) ?></p>
                    <p><strong>Struktur Folder:</strong><br><?= nl2br(e($detail['folder_structure'] ?: '-')) ?></p>
                    <?php if ($assessment): ?>
                        <h3>Nilai Seminar: <?= fmt_score($assessment['total_score']) ?></h3>
                        <p><?= nl2br(e($assessment['assessor_notes'] ?: '-')) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="ifp-hero">
            <h2>SEMINAR HASIL PKL & BEDAH KODE</h2>
            <p>Presentasi aplikasi yang dibuat siswa selama magang, dilanjutkan dengan bedah kode dan developer challenge oleh guru.</p>
        </div>

        <div class="ifp-grid cols-4">
            <?php foreach ($rows as $r): ?>
                <a class="ifp-menu" href="<?= url('ifp/seminar.php?student_id=' . (int)$r['student_id']) ?>">
                    <span class="ico">🎤</span>
                    <?= e($r['name']) ?>
                    <small><?= e($r['class_name']) ?> · <?= e(str_limit($r['app_title'], 34)) ?></small>
                    <small>
                        <?php if ($r['last_score'] !== null): ?>
                            <span class="ifp-badge green">Nilai <?= fmt_score($r['last_score']) ?></span>
                        <?php else: ?>
                            <span class="ifp-badge"><?= e(ucfirst($r['status'])) ?></span>
                        <?php endif; ?>
                    </small>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php ifp_footer(); ?>
