<?php
/** modules/challenges/detail.php - Detail tantangan, timer, editor, dan submit. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$id = (int) query('id', 1);
$challenge = db_one('SELECT * FROM coding_challenges WHERE id = ?', [$id]);
if (!$challenge) redirect('modules/challenges/index.php');
$page_title = $challenge['code'] . ' — ' . $challenge['title'];

$studentId = (int) (current_user()['student_id'] ?? 0);
$activeAttempt = $studentId
    ? db_one("SELECT * FROM coding_attempts WHERE student_id = ? AND challenge_id = ? AND status = 'berjalan' ORDER BY id DESC LIMIT 1",
             [$studentId, $id])
    : null;

$instructions = array_filter(array_map('trim', preg_split('/\R/', (string) $challenge['instructions']) ?: []));
$criteria = array_filter(array_map('trim', preg_split('/\R/', (string) $challenge['success_criteria']) ?: []));
$seconds = (int) $challenge['duration_min'] * 60;
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <p style="margin:0 0 6px"><a href="<?= url('modules/challenges/index.php') ?>">← Daftar Tantangan</a></p>
        <h1><?= e($challenge['code']) ?> — <?= e($challenge['title']) ?></h1>
        <p><?= e(CHALLENGE_LEVELS[$challenge['level']]) ?> · Kompetensi: <?= e($challenge['competency']) ?> · Durasi <?= (int) $challenge['duration_min'] ?> menit</p>
    </div>
    <div class="btn-group">
        <a class="btn btn-accent" href="<?= url('modules/code/index.php?source=challenge&id=' . $id . '&mode=tantangan') ?>">💻 Buka di Editor Kode</a>
    </div>
</div>

<div class="grid" style="grid-template-columns:1fr 1.3fr;align-items:start">
    <div>
        <div class="card">
            <h2>Deskripsi</h2>
            <p><?= nl2br(e($challenge['description'])) ?></p>

            <h2>Instruksi</h2>
            <ol style="padding-left:22px">
                <?php foreach ($instructions as $step): ?>
                    <li style="margin-bottom:8px"><?= e(preg_replace('/^\d+\.\s*/', '', $step)) ?></li>
                <?php endforeach; ?>
            </ol>

            <h2>Kriteria Keberhasilan</h2>
            <ul style="padding-left:22px">
                <?php foreach ($criteria as $crit): ?>
                    <li><?= e(preg_replace('/^[-*]\s*/', '', $crit)) ?></li>
                <?php endforeach; ?>
            </ul>

            <?php if (!empty($challenge['rubric'])): ?>
            <h2>Rubrik Penilaian</h2>
            <p><?= nl2br(e($challenge['rubric'])) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($studentId): ?>
        <div class="card" id="timer-card">
            <div class="card-head"><h2>Timer Pengerjaan</h2>
                <span class="badge badge-blue" id="timer-status"><?= $activeAttempt ? 'Berjalan' : 'Belum dimulai' ?></span>
            </div>
            <div class="text-center" style="padding:16px 0">
                <div id="timer-display" style="font-size:3.4rem;font-weight:900;font-variant-numeric:tabular-nums;line-height:1;color:var(--primary)">
                    <?= gmdate('i:s', $seconds) ?>
                </div>
                <div class="muted">menit : detik</div>
            </div>
            <div class="progress"><span id="timer-bar" style="width:100%"></span></div>
            <div class="form-actions" style="margin-top:18px">
                <?php if (!$activeAttempt): ?>
                    <form method="post" action="<?= url('modules/challenges/process.php') ?>" style="width:100%">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="start">
                        <input type="hidden" name="challenge_id" value="<?= $id ?>">
                        <button class="btn btn-primary btn-block btn-lg" type="submit">▶ Mulai Tantangan</button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-warning btn-block" type="button" onclick="flashInfo('Timer tetap berjalan. Segera kumpulkan hasil.')">⏱ Sedang Berjalan</button>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Mode guru: timer dan pengumpulan hasil tersedia untuk akun siswa. Gunakan panel penilaian untuk menilai attempt siswa.</div>
        <?php endif; ?>
    </div>

    <div>
        <div class="card">
            <div class="card-head"><h2>Starter Code</h2>
                <button class="btn btn-light btn-sm" onclick="copyText('#starter-code')">📋 Salin</button>
            </div>
            <pre class="code-block" id="starter-code" style="white-space:pre-wrap;max-height:520px"><?= e($challenge['starter_code'] ?: '// Tulis kode Anda di sini') ?></pre>
        </div>

        <?php if ($studentId): ?>
        <div class="card">
            <h2>Kumpulkan Hasil</h2>
            <?php if ($activeAttempt): ?>
                <form method="post" action="<?= url('modules/challenges/process.php') ?>">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="submit">
                    <input type="hidden" name="attempt_id" value="<?= (int) $activeAttempt['id'] ?>">
                    <div class="form-group">
                        <label>Hasil Kode / Jawaban</label>
                        <textarea name="result_text" rows="10" required style="font-family:Consolas,monospace;font-size:.85rem"><?= e($activeAttempt['result_text'] ?? $challenge['starter_code']) ?></textarea>
                    </div>
                    <button class="btn btn-accent btn-block btn-lg" type="submit">✅ Kumpulkan & Selesaikan</button>
                </form>
            <?php else: ?>
                <p class="muted">Klik <strong>Mulai Tantangan</strong> terlebih dahulu untuk mengaktifkan form pengumpulan.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($studentId && $activeAttempt): ?>
<script>
(function () {
    var total = <?= $seconds ?>;
    var started = new Date('<?= date('c', strtotime($activeAttempt['started_at'])) ?>').getTime();
    var display = document.getElementById('timer-display');
    var bar = document.getElementById('timer-bar');
    var status = document.getElementById('timer-status');
    if (!display) return;
    function tick() {
        var elapsed = Math.floor((Date.now() - started) / 1000);
        var remaining = Math.max(0, total - elapsed);
        var m = Math.floor(remaining / 60);
        var s = remaining % 60;
        display.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        bar.style.width = (remaining / total * 100) + '%';
        if (remaining === 0) {
            status.textContent = 'Waktu Habis';
            status.className = 'badge badge-red';
            display.style.color = 'var(--danger)';
        }
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
<?php endif; ?>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
