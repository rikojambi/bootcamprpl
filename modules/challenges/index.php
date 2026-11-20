<?php
/** modules/challenges/index.php - Daftar 15 tantangan live coding. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Tantangan Live Coding';

$studentId = (int) (current_user()['student_id'] ?? 0);
$levelFilter = query('level', '');
$where = $levelFilter !== '' ? 'WHERE level = ?' : '';
$params = $levelFilter !== '' ? [$levelFilter] : [];

$challenges = db_all("SELECT * FROM coding_challenges $where
    ORDER BY FIELD(level, 'beginner','intermediate','advanced'), id", $params);

// Statistik attempt siswa
$myAttempts = $studentId
    ? db_all('SELECT challenge_id, status, examiner_score FROM coding_attempts WHERE student_id = ?', [$studentId])
    : [];
$attemptMap = [];
foreach ($myAttempts as $a) {
    $attemptMap[$a['challenge_id']][] = $a;
}
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Tantangan Live Coding</h1>
        <p>15 tantangan bertingkat untuk melatih kecepatan dan ketepatan menulis kode. Timer membantu mengukur durasi pengerjaan.</p>
    </div>
    <div class="btn-group">
        <a href="<?= url('modules/challenges/history.php') ?>" class="btn btn-outline">📜 Riwayat Attempt</a>
    </div>
</div>

<div class="card">
    <div class="pill-nav">
        <a class="pill <?= $levelFilter === '' ? 'active' : '' ?>" href="<?= url('modules/challenges/index.php') ?>">Semua Level</a>
        <?php foreach (CHALLENGE_LEVELS as $key => $label): ?>
            <a class="pill <?= $levelFilter === $key ? 'active' : '' ?>" href="<?= url('modules/challenges/index.php?level=' . $key) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="grid grid-auto">
    <?php foreach ($challenges as $c): ?>
        <?php $attempts = $attemptMap[$c['id']] ?? []; ?>
        <?php $best = null; foreach ($attempts as $a) if ($a['examiner_score'] !== null && ($best === null || $a['examiner_score'] > $best)) $best = $a['examiner_score']; ?>
        <div class="card" style="margin:0;display:flex;flex-direction:column">
            <div class="card-head" style="margin-bottom:10px">
                <span class="badge badge-<?= ['beginner'=>'green','intermediate'=>'blue','advanced'=>'purple'][$c['level']] ?>"><?= e(CHALLENGE_LEVELS[$c['level']]) ?></span>
                <small class="muted">⏱ <?= (int) $c['duration_min'] ?> menit</small>
            </div>
            <h3 style="font-size:1.05rem"><?= e($c['code']) ?> — <?= e($c['title']) ?></h3>
            <p class="muted" style="font-size:.85rem;flex:1"><?= e(str_limit($c['description'], 130)) ?></p>
            <p style="font-size:.8rem"><strong>Kompetensi:</strong> <?= e($c['competency']) ?></p>
            <?php if ($best !== null): ?>
                <p><span class="badge <?= score_badge_class((float) $best) ?>">Nilai terbaik: <?= (int) $best ?></span></p>
            <?php elseif ($attempts): ?>
                <p><span class="badge badge-yellow">Sudah dicoba (<?= count($attempts) ?>)</span></p>
            <?php endif; ?>
            <a class="btn btn-primary btn-block" style="margin-top:8px" href="<?= url('modules/challenges/detail.php?id=' . (int) $c['id']) ?>">Kerjakan →</a>
        </div>
    <?php endforeach; ?>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
