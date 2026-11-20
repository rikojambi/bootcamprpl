<?php
/** modules/challenges/history.php - Riwayat attempt live coding. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Riwayat Live Coding';

$studentId = (int) (current_user()['student_id'] ?? 0);
if (can_manage()) {
    $rows = db_all('SELECT ca.*, s.name AS student_name, c.code AS challenge_code, c.title AS challenge_title,
            c.level
        FROM coding_attempts ca
        JOIN students s ON s.id = ca.student_id
        JOIN coding_challenges c ON c.id = ca.challenge_id
        ORDER BY ca.id DESC LIMIT 200');
} else {
    $rows = db_all('SELECT ca.*, c.code AS challenge_code, c.title AS challenge_title, c.level
        FROM coding_attempts ca
        JOIN coding_challenges c ON c.id = ca.challenge_id
        WHERE ca.student_id = ? ORDER BY ca.id DESC', [$studentId]);
}
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Riwayat Live Coding</h1>
        <p>Catatan seluruh sesi tantangan yang telah dijalankan.</p>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr>
                <th>ID</th>
                <?php if (can_manage()): ?><th>Siswa</th><?php endif; ?>
                <th>Tantangan</th><th>Level</th><th>Mulai</th><th>Durasi</th><th>Status</th><th>Nilai</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="10" class="text-center muted">Belum ada attempt.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td>#<?= (int) $r['id'] ?></td>
                    <?php if (can_manage()): ?><td><?= e($r['student_name']) ?></td><?php endif; ?>
                    <td><strong><?= e($r['challenge_code']) ?></strong><br><small><?= e(str_limit($r['challenge_title'], 40)) ?></small></td>
                    <td><span class="badge badge-<?= ['beginner'=>'green','intermediate'=>'blue','advanced'=>'purple'][$r['level']] ?>"><?= e(CHALLENGE_LEVELS[$r['level']]) ?></span></td>
                    <td><?= e(format_date($r['started_at'], true)) ?></td>
                    <td><?= floor((int)$r['duration_sec']/60) ?>m <?= (int)$r['duration_sec']%60 ?>d</td>
                    <td><span class="badge badge-<?= ['berjalan'=>'yellow','selesai'=>'blue','dinilai'=>'green'][$r['status']] ?>"><?= e(ucfirst($r['status'])) ?></span></td>
                    <td><?= $r['examiner_score'] !== null
                        ? '<span class="badge ' . score_badge_class((float)$r['examiner_score']) . '">' . (int)$r['examiner_score'] . '</span>'
                        : '-' ?></td>
                    <td class="actions">
                        <a class="btn btn-primary btn-sm" href="<?= url('modules/challenges/result.php?id=' . (int)$r['id']) ?>">Detail</a>
                        <?php if (can_manage() && $r['status'] !== 'dinilai'): ?>
                        <a class="btn btn-accent btn-sm" href="<?= url('modules/challenges/grade.php?id=' . (int)$r['id']) ?>">Nilai</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
