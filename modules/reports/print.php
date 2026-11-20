<?php
/** modules/reports/print.php - Cetak laporan ringkas seluruh siswa. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$ownStudentId = can_manage() ? 0 : (int) current_user()['student_id'];
$rows = db_all('SELECT s.nis, s.name, s.class_name, c.total_score,
        (SELECT COUNT(*) FROM student_jobsheets sj WHERE sj.student_id = s.id AND sj.status = "selesai") AS jobs_done
    FROM students s
    LEFT JOIN competency_assessments c ON c.id = (SELECT MAX(id) FROM competency_assessments WHERE student_id = s.id)
    WHERE s.status = "aktif" AND (? = 0 OR s.id = ?)
    ORDER BY s.class_name, s.name', [$ownStudentId, $ownStudentId]);
$totalJobs = (int) db_value('SELECT COUNT(*) FROM jobsheets');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Monitoring Siswa</title>
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<style>
body { background:#fff; }
.sheet { max-width:1000px; margin:24px auto; }
.sheet h1 { font-size:1.35rem; text-align:center; }
table.rep { width:100%; border-collapse:collapse; margin-top:18px; }
table.rep th, table.rep td { border:1px solid #999; padding:8px 10px; font-size:.8rem; text-align:left; }
table.rep th { background:#f0f3f8; }
@media print { .no-print { display:none; } body { background:#fff; } }
</style>
</head>
<body>
<div class="sheet">
    <div class="no-print btn-group" style="margin-bottom:18px">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Cetak</button>
        <button class="btn btn-light" onclick="window.close()">Tutup</button>
    </div>
    <h1><?= can_manage() ? 'LAPORAN MONITORING SISWA' : 'LAPORAN PROGRES BELAJAR' ?><br><?= e(SCHOOL_NAME) ?></h1>
    <p style="text-align:center;font-size:.85rem">Program Keahlian <?= e(PROGRAM_NAME) ?> · Tahun Ajaran <?= e(setting('academic_year')) ?> · Penyusun: <?= e(AUTHOR_NAME) ?></p>

    <table class="rep">
        <thead><tr><th>No</th><th>NIS</th><th>Nama Siswa</th><th>Kelas</th><th>Jobsheet</th><th>Nilai Kompetensi</th><th>Keterangan</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $i => $r): ?>
            <?php $score = $r['total_score'] !== null ? (float)$r['total_score'] : null; ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($r['nis']) ?></td>
                <td><?= e($r['name']) ?></td>
                <td><?= e($r['class_name']) ?></td>
                <td><?= (int)$r['jobs_done'] ?>/<?= $totalJobs ?></td>
                <td><?= $score !== null ? fmt_score($score) : '-' ?></td>
                <td><?= $score === null ? 'Belum dinilai' : e(score_predicate($score)) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div style="display:flex;justify-content:space-between;margin-top:50px;font-size:.85rem">
        <div>Wali Kelas<br><br><br>________________________</div>
        <div>Mengetahui,<br><br><br>________________________<br>Kepala Program Keahlian PPLG</div>
    </div>
</div>
</body>
</html>
