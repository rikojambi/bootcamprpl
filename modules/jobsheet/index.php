<?php
/** modules/jobsheet/index.php - Daftar 12 jobsheet + progres siswa. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Jobsheet Praktikum';

$students = can_manage()
    ? db_all('SELECT id, name, class_name FROM students ORDER BY name')
    : [];
$selectedStudent = can_manage() ? (int) query('student_id', 0) : (int) current_user()['student_id'];
if (!$selectedStudent && $students) $selectedStudent = (int) $students[0]['id'];

$jobsheets = db_all('SELECT j.*, sj.status AS my_status, sj.completed_at
    FROM jobsheets j
    LEFT JOIN student_jobsheets sj ON sj.jobsheet_id = j.id AND sj.student_id = ?
    ORDER BY j.sort_order', [$selectedStudent]);

$done = count(array_filter($jobsheets, fn($j) => $j['my_status'] === 'selesai'));
$selectedName = 'Progres Saya';
foreach ($students as $s) if ((int) $s['id'] === $selectedStudent) $selectedName = $s['name'];
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Jobsheet Praktikum</h1>
        <p>12 lembar kerja praktikum. Siswa dapat menandai penyelesaian, guru dapat memantau progres setiap siswa.</p>
    </div>
    <div class="btn-group">
        <a href="<?= url('modules/jobsheet/print.php?all=1') ?>" target="_blank" class="btn btn-outline">🖨️ Cetak Semua</a>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Progres: <?= e($selectedName) ?></h2>
        <?php if (can_manage()): ?>
        <form method="get" action="<?= url('modules/jobsheet/index.php') ?>" style="display:flex;gap:10px;align-items:center">
            <label style="font-weight:700;font-size:.85rem">Pilih Siswa</label>
            <select name="student_id" onchange="this.form.submit()" style="min-width:240px">
                <?php foreach ($students as $s): ?>
                    <option value="<?= (int) $s['id'] ?>" <?= $selectedStudent === (int) $s['id'] ? 'selected' : '' ?>>
                        <?= e($s['name']) ?> — <?= e($s['class_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php endif; ?>
    </div>
    <div class="chart-row" style="grid-template-columns:120px 1fr 80px">
        <strong><?= $done ?> / <?= count($jobsheets) ?></strong>
        <div class="progress green"><span style="width:<?= count($jobsheets) ? round($done/count($jobsheets)*100) : 0 ?>%"></span></div>
        <div class="chart-value"><?= count($jobsheets) ? round($done/count($jobsheets)*100) : 0 ?>%</div>
    </div>
</div>

<div class="card">
    <div class="card-head"><h2>Daftar Jobsheet</h2>
        <input type="text" id="table-filter" placeholder="🔍 Cari jobsheet…" style="max-width:300px">
    </div>
    <div class="table-wrap">
    <table class="data-table" data-filterable>
        <thead><tr><th>Kode</th><th>Judul</th><th>Durasi</th><th>Status</th><th>Selesai</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($jobsheets as $j): ?>
            <tr>
                <td><span class="badge badge-blue"><?= e($j['code']) ?></span></td>
                <td><strong><?= e($j['title']) ?></strong><br><small><?= e(str_limit($j['objective'], 70)) ?></small></td>
                <td><?= (int) $j['duration_min'] ?> menit</td>
                <td>
                    <span class="badge badge-<?= ['belum'=>'gray','proses'=>'yellow','selesai'=>'green'][$j['my_status'] ?? 'belum'] ?>">
                        <?= e(ucfirst($j['my_status'] ?? 'belum')) ?>
                    </span>
                </td>
                <td><?= $j['completed_at'] ? e(format_date($j['completed_at'])) : '-' ?></td>
                <td class="actions">
                    <a class="btn btn-primary btn-sm" href="<?= url('modules/jobsheet/detail.php?id=' . (int) $j['id'] . '&student_id=' . $selectedStudent) ?>">Buka</a>
                    <a class="btn btn-light btn-sm" href="<?= url('modules/jobsheet/print.php?id=' . (int) $j['id']) ?>" target="_blank">Cetak</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
