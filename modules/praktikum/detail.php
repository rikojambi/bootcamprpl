<?php
/** modules/praktikum/detail.php - Instruksi, kode, dan penjelasan tiap bagian. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$id = (int) query('id', 1);
$lab = db_one('SELECT * FROM jobsheets WHERE id = ?', [$id]);
if (!$lab) redirect('modules/praktikum/index.php');
$page_title = $lab['title'];

/**
 * Penjelasan otomatis untuk potongan kode yang umum dipakai siswa.
 * Guru tetap dapat menambahkan penjelasan lisan saat demonstrasi.
 */
function explain_code(string $code): array
{
    $patterns = [
        'prepare('    => 'prepare() menyiapkan query dengan placeholder. Data belum dimasukkan langsung ke perintah SQL, sehingga aman dari SQL injection.',
        'execute('    => 'execute() menjalankan query yang sudah disiapkan dan mengirim nilai parameter ke database.',
        'fetchAll'    => 'fetchAll() mengambil semua baris hasil query dalam bentuk array.',
        'fetch()'     => 'fetch() mengambil satu baris hasil query.',
        '$_POST'      => '$_POST berisi data yang dikirim browser melalui method POST.',
        '$_GET'       => '$_GET berisi data yang dikirim melalui URL (method GET).',
        'htmlspecialchars' => 'htmlspecialchars() mengubah karakter khusus menjadi entitas HTML agar aman dari XSS.',
        'header('     => 'header() mengirim instruksi HTTP, misalnya redirect ke halaman lain.',
        'session_start' => 'session_start() memulai session agar data pengguna bisa disimpan antarhalaman.',
        'password_verify' => 'password_verify() membandingkan password input dengan hash yang tersimpan di database.',
        'password_hash' => 'password_hash() membuat hash password yang aman disimpan di database.',
        'INSERT INTO' => 'INSERT INTO menambahkan baris baru ke dalam tabel.',
        'SELECT '     => 'SELECT mengambil data dari tabel database.',
        'UPDATE '     => 'UPDATE mengubah data yang sudah ada. Perhatikan klausa WHERE.',
        'DELETE '     => 'DELETE menghapus data. Selalu gunakan WHERE agar tidak menghapus semua baris.',
        'WHERE '      => 'WHERE membatasi query hanya pada baris yang memenuhi kondisi.',
        'ORDER BY'    => 'ORDER BY mengurutkan hasil query berdasarkan kolom tertentu.',
        'CREATE TABLE' => 'CREATE TABLE membuat struktur tabel baru di database.',
        'FOREIGN KEY' => 'FOREIGN KEY menghubungkan kolom ke primary key tabel lain.',
        '<form'       => '<form> adalah wadah input yang akan dikirim ke server.',
        '<input'      => '<input> membuat kolom isian. Atribut name menentukan kunci data saat dikirim.',
        '<button'     => '<button> membuat tombol interaksi bagi pengguna.',
        'foreach'     => 'foreach digunakan untuk mengulang setiap elemen array, misalnya setiap baris data siswa.',
        'function '   => 'function mendefinisikan blok kode yang bisa dipanggil berulang kali.',
        'phpinfo'     => 'phpinfo() menampilkan informasi konfigurasi PHP pada server lokal.',
        'fputcsv'     => 'fputcsv() menulis satu baris data ke format CSV.',
        'addEventListener' => 'addEventListener() menempelkan fungsi JavaScript ke suatu event pada elemen.',
    ];

    $lines = preg_split('/\R/', $code) ?: [];
    $result = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $found = null;
        foreach ($patterns as $needle => $description) {
            if (stripos($line, $needle) !== false) { $found = $description; break; }
        }
        $result[] = [
            'line' => str_limit($line, 120),
            'explain' => $found ?? 'Baris ini merupakan bagian dari struktur program. Perhatikan bagaimana baris ini berhubungan dengan baris lain.',
        ];
    }
    return $result;
}

$explanations = explain_code((string) $lab['source_code']);
$steps = array_filter(array_map('trim', preg_split('/\R/', (string) $lab['steps']) ?: []));
$challengeText = 'Modifikasi source code di atas, lalu tunjukkan perubahan kepada guru. ' .
    'Fokus pada: ' . $lab['reflection'];
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <p style="margin:0 0 6px"><a href="<?= url('modules/praktikum/index.php') ?>">← Daftar Praktikum</a></p>
        <h1><?= e($lab['code']) ?> — <?= e($lab['title']) ?></h1>
        <p><?= e($lab['objective']) ?></p>
    </div>
    <div class="btn-group">
        <a class="btn btn-accent" href="<?= url('modules/code/index.php?source=jobsheet&id=' . (int) $lab['id']) ?>">💻 Buka di Editor</a>
        <a class="btn btn-outline" href="<?= url('modules/jobsheet/detail.php?id=' . (int) $lab['id']) ?>">📋 Lihat Jobsheet</a>
    </div>
</div>

<div class="grid" style="grid-template-columns:1fr 1.2fr;align-items:start">
    <div>
        <div class="card">
            <h2>Alat dan Bahan</h2>
            <p><?= nl2br(e($lab['tools'])) ?></p>
            <h2>Konsep</h2>
            <p><?= nl2br(e($lab['concept'])) ?></p>
        </div>

        <div class="card">
            <h2>Langkah Kerja</h2>
            <ol style="padding-left:22px">
                <?php foreach ($steps as $step): ?>
                    <li style="margin-bottom:10px"><?= e(preg_replace('/^\d+\.\s*/', '', $step)) ?></li>
                <?php endforeach; ?>
            </ol>
        </div>

        <div class="card">
            <h2>Tantangan Modifikasi</h2>
            <p><?= e($challengeText) ?></p>
            <h2>Pertanyaan Refleksi</h2>
            <p><?= nl2br(e($lab['reflection'])) ?></p>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-head">
                <h2>Source Code</h2>
                <div class="btn-group">
                    <button class="btn btn-outline btn-sm" onclick="toggleCode()">👁️ Tampilkan Kode</button>
                    <button class="btn btn-light btn-sm" onclick="copyText('#kode-lab')">📋 Salin</button>
                </div>
            </div>
            <pre class="code-block" id="kode-lab" style="display:none;max-height:460px"><?= e($lab['source_code'] ?: '// Tidak ada source code.') ?></pre>
        </div>

        <div class="card">
            <div class="card-head"><h2>Penjelasan Setiap Bagian Kode</h2>
                <button class="btn btn-outline btn-sm" onclick="togglePenjelasan()">💡 Tampilkan / Sembunyikan</button>
            </div>
            <div id="penjelasan-kode" style="display:none">
                <?php if (!$explanations): ?>
                    <p class="muted">Belum ada penjelasan otomatis untuk kode ini.</p>
                <?php endif; ?>
                <?php foreach ($explanations as $ex): ?>
                    <div style="border-left:4px solid var(--primary);padding:10px 14px;margin-bottom:14px;background:#f7faff">
                        <code style="font-size:.82rem;color:var(--primary-dark)"><?= e($ex['line']) ?></code>
                        <p style="margin:8px 0 0;font-size:.85rem"><?= e($ex['explain']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCode() {
    var el = document.getElementById('kode-lab');
    el.style.display = (el.style.display === 'none') ? 'block' : 'none';
}
function togglePenjelasan() {
    var el = document.getElementById('penjelasan-kode');
    el.style.display = (el.style.display === 'none') ? 'block' : 'none';
}
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
