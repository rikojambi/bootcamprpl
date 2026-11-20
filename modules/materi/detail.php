<?php
/** modules/materi/detail.php - Detail satu pertemuan bootcamp. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$no = max(1, min(16, (int) query('no', 1)));
$session = db_one('SELECT * FROM bootcamp_sessions WHERE session_no = ?', [$no]);
if (!$session) redirect('modules/materi/index.php');

$studentId = (int) (current_user()['student_id'] ?? 0);
$progress = $studentId
    ? db_one('SELECT * FROM student_progress WHERE student_id = ? AND session_id = ?', [$studentId, (int) $session['id']])
    : null;
$materials = db_all('SELECT * FROM learning_materials WHERE session_id = ? ORDER BY sort_order', [(int) $session['id']]);
$questions = array_filter(array_map('trim', explode('|', (string) $session['questions'])));

$prev = max(1, $no - 1);
$next = min(16, $no + 1);
$page_title = 'Pertemuan ' . $no . ' — ' . $session['title'];
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <p style="margin:0 0 6px"><a href="<?= url('modules/materi/index.php') ?>">← Daftar Materi</a></p>
        <h1>Pertemuan <?= (int) $session['session_no'] ?>: <?= e($session['title']) ?></h1>
        <p>Minggu <?= (int) $session['week_no'] ?> · Durasi <?= (int) $session['duration_min'] ?> menit</p>
    </div>
    <div class="btn-group">
        <a class="btn btn-light" href="<?= url('modules/materi/detail.php?no=' . $prev) ?>">← Sebelumnya</a>
        <a class="btn btn-accent" href="<?= url('ifp/materi.php?no=' . $no) ?>" target="_blank">🖥️ Tampilkan di IFP</a>
        <a class="btn btn-light" href="<?= url('modules/materi/detail.php?no=' . $next) ?>">Berikutnya →</a>
    </div>
</div>

<div class="tabs">
    <button class="tab active" data-tab="tujuan">1. Tujuan & Materi</button>
    <button class="tab" data-tab="konsep">2. Konsep & Diagram</button>
    <button class="tab" data-tab="kode">3. Kode & Demo</button>
    <button class="tab" data-tab="latihan">4. Latihan</button>
    <button class="tab" data-tab="evaluasi">5. Evaluasi & Refleksi</button>
</div>

<div class="tab-container">
<section class="tab-panel active" data-panel="tujuan">
    <div class="card">
        <h2>Tujuan Pembelajaran</h2>
        <p><?= nl2br(e($session['objectives'])) ?></p>
        <h2>Materi Inti</h2>
        <p><?= nl2br(e($session['core_material'])) ?></p>
    </div>
    <?php if ($materials): ?>
    <div class="card">
        <h3>Materi Pendukung</h3>
        <?php foreach ($materials as $m): ?>
            <div style="padding:10px 0;border-bottom:1px solid var(--line)">
                <span class="badge badge-blue"><?= e($m['material_type']) ?></span>
                <strong><?= e($m['title']) ?></strong>
                <p class="muted" style="font-size:.85rem;margin:4px 0 0"><?= nl2br(e($m['content'])) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<section class="tab-panel" data-panel="konsep">
    <div class="card">
        <h2>Penjelasan Konsep</h2>
        <p style="font-size:1.02rem"><?= nl2br(e($session['concept'])) ?></p>
        <h2>Diagram / Alur</h2>
        <div class="code-block" style="background:#eef4ff;color:#16213b;white-space:pre-wrap"><?= e($session['diagram'] ?: '-') ?></div>
    </div>
</section>

<section class="tab-panel" data-panel="kode">
    <div class="card">
        <div class="card-head">
            <h2>Contoh Kode</h2>
            <div class="btn-group">
                <button class="btn btn-outline btn-sm" onclick="toggleCode()">👁️ Tampilkan / Sembunyikan Kode</button>
                <button class="btn btn-light btn-sm" onclick="copyText('#kode-contoh')">📋 Salin Kode</button>
                <button class="btn btn-light btn-sm" onclick="toggleFullscreen()">⛶ Layar Penuh</button>
            </div>
        </div>
        <pre class="code-block" id="kode-contoh" style="display:none"><?= e($session['code_example'] ?: '// Belum ada contoh kode pada pertemuan ini.') ?></pre>
        <p class="muted" style="font-size:.85rem">Kode ini hanya untuk dibaca dan dipelajari. Eksekusi kode PHP dilakukan melalui server lokal Laragon, bukan langsung di browser.</p>
        <h2>Demonstrasi Guru</h2>
        <p><?= nl2br(e($session['demo'])) ?></p>
        <p><a class="btn btn-primary" href="<?= url('modules/code/index.php?context=materi&no=' . $no) ?>">Buka di Editor Kode →</a></p>
    </div>
</section>

<section class="tab-panel" data-panel="latihan">
    <div class="card">
        <h2>Latihan</h2>
        <p><?= nl2br(e($session['exercise'])) ?></p>
        <h2>Pertanyaan Pemahaman</h2>
        <?php if ($questions): ?>
            <ol>
            <?php foreach ($questions as $q): ?><li style="margin-bottom:8px"><?= e($q) ?></li><?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p class="muted">Belum ada pertanyaan.</p>
        <?php endif; ?>
    </div>
</section>

<section class="tab-panel" data-panel="evaluasi">
    <div class="card">
        <h2>Evaluasi</h2>
        <p><?= nl2br(e($session['evaluation'])) ?></p>
        <h2>Refleksi Siswa</h2>
        <p><?= nl2br(e($session['reflection'])) ?></p>
        <div class="divider"></div>
        <?php if ($studentId && can_manage()): ?>
            <p class="muted">Mode guru: progres siswa dikelola melalui halaman Monitoring Siswa.</p>
            <a class="btn btn-primary" href="<?= url('modules/students/index.php') ?>">Buka Monitoring Siswa</a>
        <?php elseif ($studentId): ?>
            <form method="post" action="<?= url('modules/materi/process.php') ?>">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="progress">
                <input type="hidden" name="session_id" value="<?= (int) $session['id'] ?>">
                <div class="form-group">
                    <label>Status Belajar Saya</label>
                    <select name="status">
                        <?php foreach (['belum'=>'Belum dipelajari','sedang'=>'Sedang dipelajari','selesai'=>'Selesai'] as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= ($progress['status'] ?? 'belum') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Catatan Refleksi</label>
                    <textarea name="notes" rows="3"><?= e($progress['notes'] ?? '') ?></textarea></div>
                <button class="btn btn-accent" type="submit">💾 Simpan Progres</button>
            </form>
        <?php endif; ?>
    </div>
</section>
</div>

<script>
function toggleCode() {
    var el = document.getElementById('kode-contoh');
    el.style.display = (el.style.display === 'none') ? 'block' : 'none';
}
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
