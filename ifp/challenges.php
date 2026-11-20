<?php
/** ifp/challenges.php - Tampilan IFP untuk tantangan live coding. */
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/auth/auth.php';
require_once __DIR__ . '/_layout.php';

require_login();

$id = (int) query('id', 0);
$detail = $id ? db_one('SELECT * FROM coding_challenges WHERE id = ?', [$id]) : null;
$level = query('level', '');
$where = $level !== '' ? 'WHERE level = ?' : '';
$rows = db_all("SELECT * FROM coding_challenges $where ORDER BY FIELD(level,'beginner','intermediate','advanced'), id",
    $level !== '' ? [$level] : []);

ifp_header('TANTANGAN LIVE CODING', '15 tantangan · Timer · Rubrik penilaian');
?>

<main class="ifp-body wide">
    <?php if ($detail): ?>
        <div class="ifp-slide" id="ifp-slide">
            <a class="ifp-btn small" href="<?= url('ifp/challenges.php') ?>">← Daftar Tantangan</a>
            <h2 style="margin-top:18px"><?= e($detail['code']) ?> — <?= e($detail['title']) ?></h2>
            <p><span class="ifp-badge"><?= e(CHALLENGE_LEVELS[$detail['level']]) ?></span>
                <span class="ifp-badge green">⏱ <?= (int)$detail['duration_min'] ?> menit</span>
                <?= e($detail['competency']) ?></p>
            <h3>Deskripsi</h3>
            <p><?= nl2br(e($detail['description'])) ?></p>
            <h3>Instruksi</h3>
            <ol><?php foreach (preg_split('/\R/', (string)$detail['instructions']) ?: [] as $s): if (trim($s)==='') continue; ?>
                <li><?= e(preg_replace('/^\d+\.\s*/','',trim($s))) ?></li><?php endforeach; ?></ol>
            <h3>Kriteria Keberhasilan</h3>
            <p><?= nl2br(e($detail['success_criteria'])) ?></p>
            <h3>Starter Code</h3>
            <pre><?= e($detail['starter_code'] ?: '// Tulis kode di sini') ?></pre>
        </div>
    <?php else: ?>
        <div class="ifp-hero">
            <h2>LIVE CODING CHALLENGE</h2>
            <p>Latih kecepatan dan ketepatan menulis kode. Pilih tantangan sesuai level, lalu tampilkan instruksi dan starter code di layar IFP.</p>
        </div>

        <div class="pill-nav" style="margin-bottom:20px">
            <a class="ifp-btn small <?= $level === '' ? 'primary' : '' ?>" href="<?= url('ifp/challenges.php') ?>">Semua</a>
            <?php foreach (CHALLENGE_LEVELS as $key => $label): ?>
                <a class="ifp-btn small <?= $level === $key ? 'primary' : '' ?>" href="<?= url('ifp/challenges.php?level=' . $key) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>

        <div class="ifp-grid cols-3">
            <?php foreach ($rows as $r): ?>
                <a class="ifp-menu" href="<?= url('ifp/challenges.php?id=' . (int)$r['id']) ?>">
                    <span class="ico">⚡</span>
                    <?= e($r['code']) ?> — <?= e(str_limit($r['title'], 30)) ?>
                    <small><?= e(CHALLENGE_LEVELS[$r['level']]) ?> · <?= (int)$r['duration_min'] ?> menit</small>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php ifp_footer(); ?>
