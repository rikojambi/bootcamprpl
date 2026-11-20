<?php
/** modules/questions/index.php - Bank pertanyaan bedah kode. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Bank Soal Bedah Kode';

$category = query('category', '');
$where = $category !== '' ? 'WHERE category = ?' : '';
$params = $category !== '' ? [$category] : [];
$rows = db_all("SELECT * FROM question_bank $where ORDER BY FIELD(category, 'HTML','CSS','JavaScript','PHP','MySQL','API','Authentication','Debugging','Security'), id", $params);

$counts = db_all('SELECT category, COUNT(*) AS total FROM question_bank GROUP BY category');
$countMap = [];
foreach ($counts as $c) $countMap[$c['category']] = (int) $c['total'];
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Bank Soal Bedah Kode</h1>
        <p>Kumpulan pertanyaan untuk seminar, bedah kode, dan evaluasi pemahaman siswa.</p>
    </div>
    <?php if (can_manage()): ?>
    <a class="btn btn-primary" href="<?= url('modules/questions/form.php') ?>">＋ Tambah Pertanyaan</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="pill-nav">
        <a class="pill <?= $category === '' ? 'active' : '' ?>" href="<?= url('modules/questions/index.php') ?>">Semua (<?= array_sum($countMap) ?>)</a>
        <?php foreach (QUESTION_CATEGORIES as $cat): ?>
            <a class="pill <?= $category === $cat ? 'active' : '' ?>" href="<?= url('modules/questions/index.php?category=' . urlencode($cat)) ?>">
                <?= e($cat) ?> (<?= $countMap[$cat] ?? 0 ?>)
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <div class="card-head"><h2>Daftar Pertanyaan</h2>
        <input type="text" id="table-filter" placeholder="🔍 Cari pertanyaan…" style="max-width:320px">
    </div>
    <div class="table-wrap">
        <table class="data-table" data-filterable>
            <thead><tr><th>No</th><th>Kategori</th><th>Pertanyaan</th><th>Kesulitan</th><th>Jawaban</th><?php if (can_manage()): ?><th>Aksi</th><?php endif; ?></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr><td colspan="6" class="text-center muted">Belum ada pertanyaan.</td></tr><?php endif; ?>
            <?php foreach ($rows as $i => $q): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><span class="badge badge-blue"><?= e($q['category']) ?></span></td>
                    <td style="max-width:420px"><?= e($q['question']) ?></td>
                    <td><span class="badge badge-<?= ['mudah'=>'green','sedang'=>'yellow','sulit'=>'red'][$q['difficulty']] ?>"><?= e(ucfirst($q['difficulty'])) ?></span></td>
                    <td><?= $q['expected_answer'] ? e(str_limit($q['expected_answer'], 60)) : '<span class="muted">-</span>' ?></td>
                    <?php if (can_manage()): ?>
                    <td class="actions">
                        <a class="btn btn-light btn-sm" href="<?= url('modules/questions/form.php?id=' . (int)$q['id']) ?>">Edit</a>
                        <a class="btn btn-danger btn-sm" data-confirm="Hapus pertanyaan ini?" href="<?= url('modules/questions/process.php?action=delete&id=' . (int)$q['id']) ?>">Hapus</a>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
