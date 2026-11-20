<?php
/** modules/jobsheet/print.php - Cetak jobsheet (satu atau semua). */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$all = query('all') === '1';
$id = (int) query('id', 1);

if ($all) {
    $jobs = db_all('SELECT * FROM jobsheets ORDER BY sort_order');
    $title = 'Semua Jobsheet Praktikum';
} else {
    $row = db_one('SELECT * FROM jobsheets WHERE id = ?', [$id]);
    if (!$row) exit('Jobsheet tidak ditemukan.');
    $jobs = [$row];
    $title = $row['code'] . ' — ' . $row['title'];
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cetak Jobsheet — <?= e($title) ?></title>
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<style>
body { background:#fff; }
.sheet { max-width:960px; margin:24px auto; }
.sheet h1 { font-size:1.4rem; }
.job-block { border:2px solid #333; border-radius:8px; padding:18px; margin-bottom:22px; page-break-inside:avoid; }
.job-block h2 { font-size:1.15rem; border-bottom:2px solid #333; padding-bottom:6px; }
.job-block h3 { font-size:1rem; margin-top:14px; }
.job-block pre { background:#f4f4f4; border:1px solid #bbb; padding:12px; white-space:pre-wrap; font-size:.8rem; }
.meta { display:flex; gap:20px; flex-wrap:wrap; font-size:.82rem; margin-bottom:10px; }
.sign { margin-top:30px; display:flex; justify-content:space-between; font-size:.85rem; }
@media print { .no-print { display:none; } body { background:#fff; } }
</style>
</head>
<body>
<div class="sheet">
    <div class="no-print btn-group" style="margin-bottom:18px">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Cetak</button>
        <button class="btn btn-light" onclick="window.close()">Tutup</button>
    </div>
    <h1>LEMBAR JOBSHEET PRAKTIKUM<br><?= e(SCHOOL_NAME) ?></h1>
    <p style="font-size:.85rem">Program Keahlian <?= e(PROGRAM_NAME) ?> · Penyusun: <?= e(AUTHOR_NAME) ?></p>

    <?php foreach ($jobs as $j): ?>
    <div class="job-block">
        <h2><?= e($j['code']) ?> — <?= e($j['title']) ?></h2>
        <div class="meta">
            <span><strong>Durasi:</strong> <?= (int) $j['duration_min'] ?> menit</span>
            <span><strong>Nama Siswa:</strong> ________________________</span>
            <span><strong>Kelas:</strong> ____________________</span>
        </div>
        <h3>Tujuan</h3><p><?= nl2br(e($j['objective'])) ?></p>
        <h3>Alat dan Bahan</h3><p><?= nl2br(e($j['tools'])) ?></p>
        <h3>Konsep</h3><p><?= nl2br(e($j['concept'])) ?></p>
        <h3>Langkah Kerja</h3>
        <ol><?php foreach (preg_split('/\R/', (string) $j['steps']) ?: [] as $s): if (trim($s) === '') continue; ?>
            <li><?= e(preg_replace('/^\d+\.\s*/', '', trim($s))) ?></li><?php endforeach; ?></ol>
        <h3>Source Code</h3><pre><?= e($j['source_code']) ?></pre>
        <h3>Tugas</h3><p><?= nl2br(e($j['task'])) ?></p>
        <h3>Pertanyaan Refleksi</h3><p><?= nl2br(e($j['reflection'])) ?></p>
        <h3>Checklist Penyelesaian</h3>
        <p>☐ Membaca instruksi &nbsp; ☐ Menjalankan kode &nbsp; ☐ Menjawab refleksi &nbsp; ☐ Menyerahkan tugas</p>
        <div class="sign"><div>Nilai: __________</div><div>Paraf Guru: ______________</div></div>
    </div>
    <?php endforeach; ?>
</div>
</body>
</html>
