<?php
/** modules/assessment/print.php - Cetak lembar penilaian kompetensi. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$studentId = can_manage() ? (int) query('student_id', 0) : (int) current_user()['student_id'];
$student = db_one('SELECT * FROM students WHERE id = ?', [$studentId]);
if (!$student) exit('Siswa tidak ditemukan.');
$a = db_one('SELECT c.*, u.full_name AS assessor_name FROM competency_assessments c
    LEFT JOIN users u ON u.id = c.assessor_id WHERE c.student_id = ? ORDER BY c.id DESC LIMIT 1', [$studentId]);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Lembar Penilaian Kompetensi — <?= e($student['name']) ?></title>
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<style>
body { background:#fff; }
.sheet { max-width:900px; margin:24px auto; }
.sheet h1 { font-size:1.35rem; text-align:center; }
table.observ { width:100%; border-collapse:collapse; margin-bottom:18px; }
table.observ th, table.observ td { border:1px solid #999; padding:10px; font-size:.85rem; text-align:left; }
table.observ th { background:#f0f3f8; }
@media print { .no-print { display:none; } body { background:#fff; } }
</style>
</head>
<body>
<div class="sheet">
    <div class="no-print btn-group" style="margin-bottom:18px">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Cetak</button>
        <button class="btn btn-light" onclick="window.close()">Tutup</button>
    </div>
    <h1>LEMBAR PENILAIAN KOMPETENSI<br><?= e(SCHOOL_NAME) ?></h1>
    <p style="text-align:center;font-size:.85rem">Program Keahlian <?= e(PROGRAM_NAME) ?> · Penyusun: <?= e(AUTHOR_NAME) ?></p>

    <table class="observ">
        <tr><th>Nama Siswa</th><td><?= e($student['name']) ?></td><th>Kelas</th><td><?= e($student['class_name']) ?></td></tr>
        <tr><th>NIS</th><td><?= e($student['nis']) ?></td><th>Tanggal</th><td><?= $a ? e(format_date($a['assessment_date'])) : '-' ?></td></tr>
    </table>

    <h3>Aspek Penilaian</h3>
    <table class="observ">
        <tr><th>No</th><th>Aspek</th><th>Bobot</th><th>Skor (1-4)</th><th>Nilai Terbobot</th></tr>
        <?php if ($a): ?>
            <?php $no=1; foreach (COMPETENCY_LABELS as $key => $label): ?>
                <tr><td><?= $no++ ?></td><td><?= e($label) ?></td><td><?= COMPETENCY_WEIGHTS[$key] ?>%</td>
                    <td><?= (int)$a[$key] ?></td>
                    <td><?= fmt_score(((int)$a[$key]/4)*COMPETENCY_WEIGHTS[$key]) ?></td></tr>
            <?php endforeach; ?>
            <tr><th colspan="4">NILAI TOTAL</th><th><?= fmt_score($a['total_score']) ?> — <?= e(score_predicate((float)$a['total_score'])) ?></th></tr>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center">Belum dinilai</td></tr>
        <?php endif; ?>
    </table>

    <h3>Catatan Asesor</h3>
    <p><?= nl2br(e($a['notes'] ?? '_______________________________________________________________________')) ?></p>

    <div style="display:flex;justify-content:space-between;margin-top:50px;font-size:.85rem">
        <div>Asesor<br><br><br>________________________<br><?= e($a['assessor_name'] ?? AUTHOR_NAME) ?></div>
        <div>Mengetahui,<br><br><br>________________________<br>Kepala Program Keahlian PPLG</div>
    </div>
</div>
</body>
</html>
