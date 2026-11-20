<?php
/** modules/seminar/print.php - Lembar observasi & catatan asesor (cetak). */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();
$studentId = (int) query('id', 0);
$student = db_one('SELECT * FROM students WHERE id = ?', [$studentId]);
if (!$student) exit('Siswa tidak ditemukan.');

$profile = db_one('SELECT * FROM seminar_profiles WHERE student_id = ?', [$studentId]);
$assessment = db_one('SELECT * FROM seminar_assessments WHERE student_id = ? ORDER BY id DESC LIMIT 1', [$studentId]);
$questions = [];
if ($assessment && !empty($assessment['selected_question_ids'])) {
    $ids = array_map('intval', explode(',', $assessment['selected_question_ids']));
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $questions = db_all("SELECT * FROM question_bank WHERE id IN ($placeholders)", $ids);
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Lembar Observasi Seminar — <?= e($student['name']) ?></title>
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<style>
body { background:#fff; }
.sheet { max-width:900px; margin:30px auto; padding:30px; }
.sheet h1 { font-size:1.4rem; text-align:center; }
table.observ { width:100%; border-collapse:collapse; margin-bottom:18px; }
table.observ th, table.observ td { border:1px solid #999; padding:10px; font-size:.88rem; text-align:left; }
table.observ th { background:#f0f3f8; }
.signature { display:flex; justify-content:space-between; margin-top:50px; font-size:.9rem; }
@media print { .no-print { display:none; } body { background:#fff; } }
</style>
</head>
<body>
<div class="sheet">
    <div class="no-print btn-group" style="margin-bottom:18px">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Cetak Lembar</button>
        <button class="btn btn-light" onclick="window.close()">Tutup</button>
    </div>

    <h1>LEMBAR OBSERVASI SEMINAR HASIL PKL<br><?= e(SCHOOL_NAME) ?></h1>
    <p style="text-align:center;font-size:.85rem">Program Keahlian <?= e(PROGRAM_NAME) ?> · Penyusun: <?= e(AUTHOR_NAME) ?></p>

    <table class="observ">
        <tr><th>Nama Siswa</th><td><?= e($student['name']) ?></td><th>Kelas</th><td><?= e($student['class_name']) ?></td></tr>
        <tr><th>Tempat PKL</th><td><?= e($profile['pkl_place'] ?? $student['pkl_place']) ?></td><th>Judul Aplikasi</th><td colspan="3"><?= e($profile['app_title'] ?? $student['pkl_title']) ?></td></tr>
        <tr><th>Teknologi</th><td colspan="5"><?= nl2br(e($profile['technologies'] ?? '')) ?></td></tr>
        <tr><th>Deskripsi Masalah</th><td colspan="5"><?= nl2br(e($profile['problem_desc'] ?? '')) ?></td></tr>
        <tr><th>Fitur Aplikasi</th><td colspan="5"><?= nl2br(e($profile['features'] ?? '')) ?></td></tr>
    </table>

    <h3>A. Penilaian Seminar</h3>
    <table class="observ">
        <tr><th>No</th><th>Aspek</th><th>Bobot</th><th>Skor (0-100)</th><th>Nilai Terbobot</th></tr>
        <?php if ($assessment): ?>
            <?php $no=1; foreach (SEMINAR_LABELS as $key=>$label): ?>
                <tr><td><?= $no++ ?></td><td><?= e($label) ?></td><td><?= SEMINAR_WEIGHTS[$key] ?>%</td>
                    <td><?= (int) $assessment[$key] ?></td>
                    <td><?= fmt_score(((int)$assessment[$key] / 100) * SEMINAR_WEIGHTS[$key]) ?></td></tr>
            <?php endforeach; ?>
            <tr><th colspan="4">NILAI TOTAL</th><th><?= fmt_score($assessment['total_score']) ?></th></tr>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center">Belum dinilai</td></tr>
        <?php endif; ?>
    </table>

    <h3>B. Pertanyaan Bedah Kode</h3>
    <?php if ($questions): ?>
        <ol>
        <?php foreach ($questions as $q): ?>
            <li><strong>[<?= e($q['category']) ?>]</strong> <?= e($q['question']) ?>
                <?php if (!empty($q['expected_answer'])): ?><br><small>Jawaban: <?= e($q['expected_answer']) ?></small><?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ol>
    <?php else: ?>
        <p>Belum ada pertanyaan yang dipilih.</p>
    <?php endif; ?>

    <h3>C. Lembar Observasi</h3>
    <p><?= nl2br(e($assessment['observation_sheet'] ?? '_______________________________________________________________________')) ?></p>

    <h3>D. Catatan Asesor</h3>
    <p><?= nl2br(e($assessment['assessor_notes'] ?? '_______________________________________________________________________')) ?></p>

    <div class="signature">
        <div>Penguji / Asesor<br><br><br>________________________<br><?= e(AUTHOR_NAME) ?></div>
        <div>Mengetahui,<br><br><br>________________________<br>Kepala Program Keahlian PPLG</div>
    </div>
</div>
</body>
</html>
