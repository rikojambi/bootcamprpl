<?php
/** modules/code/index.php - Penampil & editor kode lokal untuk pembelajaran. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Editor & Demonstrasi Kode';

// Tentukan sumber kode
$source = query('source', 'sample');
$code = '';
$sourceLabel = 'Contoh Kode Default';
$language = 'php';
$challenge = '';

if ($source === 'jobsheet') {
    $id = (int) query('id', 1);
    $row = db_one('SELECT code, title, source_code, reflection FROM jobsheets WHERE id = ?', [$id]);
    if ($row) {
        $code = (string) $row['source_code'];
        $sourceLabel = $row['code'] . ' — ' . $row['title'];
        $challenge = 'Modifikasi kode di atas sesuai tantangan: ' . $row['reflection'];
    }
} elseif ($source === 'materi') {
    $no = (int) query('no', 1);
    $row = db_one('SELECT session_no, title, code_example FROM bootcamp_sessions WHERE session_no = ?', [$no]);
    if ($row) {
        $code = (string) $row['code_example'];
        $sourceLabel = 'Pertemuan ' . $row['session_no'] . ' — ' . $row['title'];
        $challenge = 'Ubah contoh kode pada pertemuan ini, lalu jelaskan perubahan yang kamu lakukan.';
    }
} elseif ($source === 'challenge') {
    $id = (int) query('id', 1);
    $row = db_one('SELECT code, title, starter_code, instructions FROM coding_challenges WHERE id = ?', [$id]);
    if ($row) {
        $code = (string) $row['starter_code'];
        $sourceLabel = $row['code'] . ' — ' . $row['title'];
        $challenge = (string) $row['instructions'];
    }
}

if ($code === '') {
    $code = '<?php' . "\n" .
        '// Contoh kode dasar PHP + HTML' . "\n" .
        '$nama = "Ahmad Fauzi";' . "\n" .
        '$kelas = "XII PPLG 1";' . "\n" .
        '?>' . "\n" .
        '<!doctype html>' . "\n" .
        '<html lang="id">' . "\n" .
        '<head><meta charset="utf-8"><title>Contoh</title></head>' . "\n" .
        '<body>' . "\n" .
        '  <h1>Data Siswa</h1>' . "\n" .
        '  <p>Nama : <?= htmlspecialchars($nama) ?></p>' . "\n" .
        '  <p>Kelas: <?= htmlspecialchars($kelas) ?></p>' . "\n" .
        '</body>' . "\n" .
        '</html>';
}

$mode = query('mode', 'lihat');
if (!in_array($mode, ['lihat','pelajari','modifikasi','tantangan'], true)) $mode = 'lihat';

// Pilihan sumber untuk dipilih guru
$sourceOptions = db_all('SELECT id, code, title FROM jobsheets ORDER BY sort_order');
$sessionOptions = db_all('SELECT session_no, title FROM bootcamp_sessions ORDER BY session_no');
$challengeOptions = db_all('SELECT id, code, title FROM coding_challenges ORDER BY id');

// Penjelasan kode berdasarkan pola
function explain_line(string $line): string
{
    $map = [
        '$_POST' => 'Membaca data yang dikirim dari form dengan method POST.',
        '$_GET' => 'Membaca data dari URL dengan method GET.',
        'prepare' => 'Menyiapkan query SQL agar aman dari injeksi.',
        'execute' => 'Menjalankan query dengan nilai parameter yang diberikan.',
        'fetch' => 'Mengambil hasil query dari database.',
        'htmlspecialchars' => 'Mengamankan teks sebelum ditampilkan ke browser.',
        'INSERT' => 'Menambah data baru ke tabel.',
        'SELECT' => 'Mengambil data dari tabel.',
        'UPDATE' => 'Mengubah data yang sudah ada.',
        'DELETE' => 'Menghapus data dari tabel.',
        'session' => 'Mengelola data sesi pengguna.',
        '<form' => 'Membuat formulir pengirim data ke server.',
        '<input' => 'Membuat kolom isian pada formulir.',
        'function' => 'Mendefinisikan fungsi yang bisa dipakai berulang.',
        'foreach' => 'Mengulang setiap item dalam array.',
    ];
    foreach ($map as $key => $value) {
        if (stripos($line, $key) !== false) return $value;
    }
    return 'Bagian dari struktur program. Perhatikanhubungannya dengan baris lain.';
}

$codeLines = preg_split('/\R/', $code) ?: [];
$codeHtml = highlight_string($code, true);
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Editor & Demonstrasi Kode</h1>
        <p>Sumber: <strong><?= e($sourceLabel) ?></strong> · Mode: <strong><?= e(ucfirst($mode)) ?></strong></p>
    </div>
    <div class="btn-group">
        <a class="btn btn-light" href="<?= url('modules/praktikum/index.php') ?>">← Kembali</a>
        <button class="btn btn-outline" onclick="toggleFullscreen()">⛶ Layar Penuh</button>
    </div>
</div>

<!-- Pilih sumber kode -->
<div class="card">
    <form method="get" action="<?= url('modules/code/index.php') ?>" class="form-grid" style="align-items:end;gap:12px">
        <div class="form-group" style="margin:0">
            <label>Sumber Kode</label>
            <select name="source" id="source-select" onchange="updateSourceOptions()">
                <option value="sample" <?= $source === 'sample' ? 'selected' : '' ?>>Contoh Default</option>
                <option value="jobsheet" <?= $source === 'jobsheet' ? 'selected' : '' ?>>Jobsheet / Praktikum</option>
                <option value="materi" <?= $source === 'materi' ? 'selected' : '' ?>>Materi Bootcamp</option>
                <option value="challenge" <?= $source === 'challenge' ? 'selected' : '' ?>>Tantangan Live Coding</option>
            </select>
        </div>
        <div class="form-group" style="margin:0" id="detail-source-wrap">
            <label>Pilih Item</label>
            <select name="id" id="jobsheet-select" <?= $source !== 'jobsheet' && $source !== 'challenge' ? 'style="display:none"' : '' ?>>
                <?php if ($source === 'challenge'): ?>
                    <?php foreach ($challengeOptions as $o): ?>
                        <option value="<?= (int) $o['id'] ?>" <?= (int) query('id',0) === (int) $o['id'] ? 'selected' : '' ?>><?= e($o['code'] . ' - ' . $o['title']) ?></option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($sourceOptions as $o): ?>
                        <option value="<?= (int) $o['id'] ?>" <?= (int) query('id',1) === (int) $o['id'] ? 'selected' : '' ?>><?= e($o['code'] . ' - ' . $o['title']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <select name="no" id="session-select" <?= $source !== 'materi' ? 'style="display:none"' : '' ?>>
                <?php foreach ($sessionOptions as $o): ?>
                    <option value="<?= (int) $o['session_no'] ?>" <?= (int) query('no',1) === (int) $o['session_no'] ? 'selected' : '' ?>>Pertemuan <?= (int) $o['session_no'] ?> - <?= e(str_limit($o['title'], 55)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0">
            <label>Mode</label>
            <select name="mode">
                <?php foreach (['lihat'=>'1. Lihat Kode','pelajari'=>'2. Pelajari Kode','modifikasi'=>'3. Modifikasi Kode','tantangan'=>'4. Tantangan'] as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= $mode === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0"><button class="btn btn-primary" type="submit">Buka Kode</button></div>
    </form>
</div>

<?php if ($mode === 'tantangan'): ?>
<div class="alert alert-warning"><strong>Tantangan:</strong><br><?= nl2br(e($challenge ?: 'Modifikasi kode sesuai instruksi guru.')) ?></div>
<?php endif; ?>

<div class="grid" style="grid-template-columns:<?= in_array($mode, ['pelajari','tantangan'], true) ? '1.5fr 1fr' : '1fr' ?>;align-items:start">
<div>
<div class="editor-wrap">
    <div class="editor-toolbar">
        <span class="badge badge-blue">PHP / HTML / CSS / JS / SQL</span>
        <button class="btn btn-light" onclick="copyText('#copy-source')">📋 Salin Kode</button>
        <button class="btn btn-light" onclick="resetCode()">↻ Reset Kode</button>
        <button class="btn btn-light" onclick="changeFontSize(-1)">A−</button>
        <button class="btn btn-light" onclick="changeFontSize(1)">A＋</button>
        <?php if ($mode === 'modifikasi' || $mode === 'tantangan'): ?>
            <button class="btn btn-accent" onclick="saveLocalCode()">💾 Simpan Sementara</button>
        <?php endif; ?>
    </div>

    <?php if ($mode === 'lihat' || $mode === 'pelajari'): ?>
        <pre class="code-block" style="margin:0;border-radius:0;white-space:pre-wrap;max-height:560px"><?= $codeHtml ?></pre>
    <?php else: ?>
        <div class="editor-body">
            <div class="line-numbers" id="line-numbers">1</div>
            <textarea class="code-input" id="code-input" spellcheck="false"><?= e($code) ?></textarea>
        </div>
    <?php endif; ?>
</div>
<textarea id="copy-source" style="display:none"><?= e($code) ?></textarea>

<p class="muted" style="font-size:.82rem;margin-top:10px">
    Catatan: editor ini adalah media pembelajaran. Kode PHP tidak dieksekusi di browser; eksekusi dilakukan melalui server lokal Laragon.
</p>
</div>

<?php if ($mode === 'pelajari' || $mode === 'tantangan'): ?>
<div class="card">
    <h2>Panel Penjelasan Kode</h2>
    <p class="muted" style="font-size:.85rem">Sorot bagian kode di bawah untuk melihat penjelasannya.</p>
    <div id="explain-list">
        <?php foreach ($codeLines as $i => $line): ?>
            <?php if (trim($line) === '') continue; ?>
            <div style="border-bottom:1px solid var(--line);padding:8px 0;cursor:pointer"
                 onclick="showExplain(<?= $i ?>)">
                <small class="muted">Baris <?= $i + 1 ?></small><br>
                <code style="font-size:.8rem"><?= e(str_limit($line, 100)) ?></code>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="explain-panel" id="explain-panel" style="margin-top:16px">
        <h4>Penjelasan</h4>
        <p id="explain-text">Klik salah satu baris kode untuk melihat penjelasannya.</p>
    </div>
</div>
<?php endif; ?>
</div>

<script>
const explanations = <?= json_encode(array_map('explain_line', $codeLines), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const originalCode = <?= json_encode($code, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

function showExplain(index) {
    document.getElementById('explain-text').textContent = explanations[index] || 'Penjelasan belum tersedia.';
}
function resetCode() {
    const input = document.getElementById('code-input');
    if (input) {
        input.value = originalCode;
        if (window.refreshLineNumbers) refreshLineNumbers();
        flashInfo('Kode dikembalikan ke kondisi awal.');
    } else {
        flashInfo('Mode lihat tidak dapat diubah. Pilih mode Modifikasi.');
    }
}
function saveLocalCode() {
    const input = document.getElementById('code-input');
    if (!input) return;
    try {
        localStorage.setItem('bootcamp_code_draft', input.value);
        flashInfo('Kode tersimpan sementara di browser ini.');
    } catch (e) { flashInfo('Penyimpanan lokal tidak tersedia.'); }
}
function updateSourceOptions() {
    const source = document.getElementById('source-select').value;
    document.getElementById('jobsheet-select').style.display =
        (source === 'jobsheet' || source === 'challenge') ? '' : 'none';
    document.getElementById('session-select').style.display = source === 'materi' ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', function () {
    try {
        const draft = localStorage.getItem('bootcamp_code_draft');
        const input = document.getElementById('code-input');
        if (draft && input && input.value === originalCode) {
            // Jangan otomatis menimpa; guru dapat menekan reset bila ingin kode asli.
        }
    } catch (e) {}
});
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
