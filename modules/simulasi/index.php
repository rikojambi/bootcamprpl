<?php
/** modules/simulasi/index.php - Simulasi interaktif FRONTEND → BACKEND → DATABASE. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Simulasi Arsitektur Fullstack';

/**
 * Data simulasi. Setiap langkah memiliki komponen aktif, judul, penjelasan, dan kode.
 * Kode ditulis sebagai teks agar mudah dipelajari siswa (tidak dieksekusi di sini).
 */
$simulations = [
    'CREATE' => [
        'label' => 'Tambah Data (CREATE)',
        'steps' => [
            ['frontend', 'Pengguna mengisi formulir',
             'Guru mengetik nama dan kelas siswa pada formulir di browser. Form menggunakan method POST agar data tidak tampil di URL.',
             '<form method="post" action="simpan.php">\n  <input type="text" name="nama" placeholder="Nama siswa">\n  <input type="text" name="kelas" placeholder="Kelas">\n  <button type="submit">Simpan</button>\n</form>'],
            ['frontend', 'Browser mengirim POST',
             'Setelah tombol Simpan ditekan, browser membungkus data menjadi HTTP request POST dan mengirimkannya ke server Apache/PHP.',
             'POST /simpan.php HTTP/1.1\nHost: localhost\nContent-Type: application/x-www-form-urlencoded\n\nnama=Ahmad+Fauzi&kelas=XII+PPLG+1'],
            ['backend', 'PHP menerima data',
             'PHP membaca isi request melalui superglobal $_POST. Data masih berupa teks dan belum dipercaya begitu saja.',
             '<?php\n$nama = $_POST["nama"] ?? "";\n$kelas = $_POST["kelas"] ?? "";\n?>'],
            ['backend', 'Validasi input',
             'Sebelum disimpan, PHP memeriksa apakah data kosong dan membersihkan spasi. Jika tidak valid, proses dihentikan.',
             '<?php\nif (trim($nama) === "" || trim($kelas) === "") {\n  die("Nama dan kelas wajib diisi");\n}\n$nama = trim($nama);\n?>'],
            ['backend', 'Menyiapkan query INSERT',
             'PHP menggunakan prepared statement dengan tanda ? sebagai pengganti nilai. Ini mencegah SQL injection.',
             '<?php\n$sql = "INSERT INTO siswa (nama, kelas) VALUES (?, ?)";\n$stmt = $pdo->prepare($sql);\n$stmt->execute([$nama, $kelas]);\n?>'],
            ['database', 'Database menyimpan data',
             'MySQL menerima query, membuat satu baris baru di tabel siswa, lalu memberi kode sukses kembali ke PHP.',
             'INSERT INTO siswa (nama, kelas)\nVALUES ("Ahmad Fauzi", "XII PPLG 1");\n\n-- 1 row affected'],
            ['backend', 'PHP mengirim response',
             'PHP memberikan respon berupa redirect ke halaman daftar agar form tidak terkirim ulang saat halaman dimuat ulang.',
             '<?php\nheader("Location: index.php?pesan=sukses");\nexit;\n?>'],
            ['frontend', 'Hasil tampil di browser',
             'Browser mengikuti redirect dan menampilkan tabel siswa. Data baru kini tampil sebagai baris terakhir.',
             '<table>\n  <tr><th>Nama</th><th>Kelas</th></tr>\n  <tr><td>Ahmad Fauzi</td><td>XII PPLG 1</td></tr>\n</table>'],
        ],
    ],
    'READ' => [
        'label' => 'Lihat Data (READ)',
        'steps' => [
            ['frontend', 'Pengguna membuka halaman daftar',
             'Guru membuka index.php. Browser mengirim permintaan GET ke server tanpa membawa data formulir.',
             'GET /index.php HTTP/1.1\nHost: localhost'],
            ['backend', 'PHP menjalankan query SELECT',
             'PHP menyiapkan query SELECT untuk mengambil seluruh baris siswa, diurutkan dari yang terbaru.',
             '<?php\n$data = $pdo\n  ->query("SELECT * FROM siswa ORDER BY id DESC")\n  ->fetchAll();\n?>'],
            ['database', 'MySQL mengambil baris',
             'Database membaca tabel siswa dan mengembalikan kumpulan baris. Setiap baris berisi id, nama, dan kelas.',
             'SELECT * FROM siswa ORDER BY id DESC;\n\n+----+-------------+------------+\n| id | nama        | kelas      |\n+----+-------------+------------+\n| 3  | Rizky       | XII PPLG 1 |\n| 2  | Siti        | XII PPLG 1 |\n+----+-------------+------------+'],
            ['backend', 'PHP mengubah data menjadi HTML',
             'PHP melakukan perulangan foreach terhadap hasil query. Setiap nilai di-escape agar aman sebelum ditampilkan.',
             '<?php foreach ($data as $row): ?>\n  <tr>\n    <td><?= htmlspecialchars($row["nama"]) ?></td>\n    <td><?= htmlspecialchars($row["kelas"]) ?></td>\n  </tr>\n<?php endforeach; ?>'],
            ['frontend', 'Browser merender tabel',
             'Browser menerima HTML dari PHP lalu menampilkan tabel. Siswa melihat data yang sebenarnya tersimpan di database.',
             '<table class="data-table">\n  <tr><td>Rizky</td><td>XII PPLG 1</td></tr>\n  <tr><td>Siti</td><td>XII PPLG 1</td></tr>\n</table>'],
        ],
    ],
    'UPDATE' => [
        'label' => 'Ubah Data (UPDATE)',
        'steps' => [
            ['frontend', 'Pengguna menekan tombol Edit',
             'Pada baris siswa, guru menekan tombol Edit. Browser mengirim id siswa melalui URL agar PHP tahu data mana yang diubah.',
             '<a href="edit.php?id=2">Edit</a>'],
            ['backend', 'PHP mengambil data lama',
             'PHP menerima id, lalu menjalankan SELECT dengan WHERE id = ? untuk mengisi form dengan nilai lama.',
             '<?php\n$id = (int) $_GET["id"];\n$stmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");\n$stmt->execute([$id]);\n$row = $stmt->fetch();\n?>'],
            ['frontend', 'Form menampilkan data lama',
             'Browser menampilkan form edit yang sudah terisi. Nilai lama dibungkus htmlspecialchars agar aman.',
             '<input type="hidden" name="id" value="<?= $row["id"] ?>">\n<input type="text" name="nama" value="<?= htmlspecialchars($row["nama"]) ?>">'],
            ['backend', 'PHP menjalankan UPDATE',
             'Setelah form disubmit, PHP menjalankan UPDATE. Klausa WHERE wajib ada agar hanya satu baris yang berubah.',
             '<?php\n$sql = "UPDATE siswa SET nama = ?, kelas = ? WHERE id = ?";\n$stmt = $pdo->prepare($sql);\n$stmt->execute([$nama, $kelas, $id]);\n?>'],
            ['database', 'Database memperbarui baris',
             'MySQL mencari baris dengan id yang sesuai lalu mengganti nilainya. Jumlah baris terdampak biasanya 1.',
             'UPDATE siswa SET nama = "Siti Nurhaliza" WHERE id = 2;\n\n-- 1 row affected'],
            ['backend', 'PHP mengarahkan kembali',
             'Setelah berhasil, PHP melakukan redirect ke halaman daftar agar pengguna melihat hasil perubahan.',
             '<?php\nheader("Location: index.php?pesan=diubah");\nexit;\n?>'],
            ['frontend', 'Tabel menampilkan data baru',
             'Browser memuat ulang daftar siswa. Nama yang sudah diubah tampil pada baris yang bersangkutan.',
             '<tr><td>Siti Nurhaliza</td><td>XII PPLG 1</td></tr>'],
        ],
    ],
    'DELETE' => [
        'label' => 'Hapus Data (DELETE)',
        'steps' => [
            ['frontend', 'Konfirmasi penghapusan',
             'Sebelum menghapus, browser menampilkan dialog konfirmasi. Ini mencegah penghapusan yang tidak disengaja.',
             '<form method="post" action="hapus.php"\n  onsubmit="return confirm(\'Yakin hapus data ini?\')">\n  <input type="hidden" name="id" value="2">\n  <button type="submit">Hapus</button>\n</form>'],
            ['backend', 'PHP menerima id hapus',
             'PHP membaca id dari POST lalu memastikan nilainya benar-benar angka.',
             '<?php\n$id = (int) $_POST["id"];\nif ($id < 1) die("Id tidak valid");\n?>'],
            ['backend', 'Menyiapkan query DELETE',
             'Gunakan prepared statement dengan WHERE id = ?. Tanpa WHERE, semua data dalam tabel akan terhapus.',
             '<?php\n$stmt = $pdo->prepare("DELETE FROM siswa WHERE id = ?");\n$stmt->execute([$id]);\n?>'],
            ['database', 'Database menghapus baris',
             'MySQL menghapus tepat satu baris. Jika ada tabel lain yang merujuk ke baris ini, foreign key menjaga integritas data.',
             'DELETE FROM siswa WHERE id = 2;\n\n-- 1 row affected'],
            ['frontend', 'Daftar diperbarui',
             'Setelah redirect, browser menampilkan daftar siswa tanpa baris yang dihapus.',
             '<div class="alert alert-success">Data berhasil dihapus.</div>'],
        ],
    ],
    'LOGIN' => [
        'label' => 'Login & Session',
        'steps' => [
            ['frontend', 'Pengguna mengisi form login',
             'Guru memasukkan username dan password. Password dikirim melalui POST, bukan GET.',
             '<form method="post" action="login.php">\n  <input type="text" name="username">\n  <input type="password" name="password">\n  <button type="submit">Masuk</button>\n</form>'],
            ['backend', 'PHP mencari user',
             'PHP menyiapkan query SELECT berdasarkan username dengan prepared statement.',
             '<?php\n$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");\n$stmt->execute([$username]);\n$user = $stmt->fetch();\n?>'],
            ['database', 'MySQL mengembalikan data user',
             'Database mengembalikan baris user yang berisi hash password, bukan password asli.',
             'SELECT id, username, password, role FROM users\nWHERE username = "guru";'],
            ['backend', 'Verifikasi password',
             'PHP membandingkan password yang diketik dengan hash menggunakan password_verify. Password asli tidak pernah disimpan.',
             '<?php\nif ($user && password_verify($password, $user["password"])) {\n  // login berhasil\n}\n?>'],
            ['backend', 'Membuat session',
             'Session ID diperbarui untuk mencegah session fixation, lalu id user disimpan di session.',
             '<?php\nsession_regenerate_id(true);\n$_SESSION["user_id"] = $user["id"];\n$_SESSION["role"] = $user["role"];\n?>'],
            ['frontend', 'Halaman terlindungi terbuka',
             'Browser mengikuti redirect ke dashboard. Setiap halaman memeriksa session; tanpa login, pengguna dikembalikan ke halaman login.',
             '<?php\nif (empty($_SESSION["user_id"])) {\n  header("Location: login.php");\n  exit;\n}\n?>'],
            ['backend', 'Logout menghapus session',
             'Saat logout, seluruh data session dihapus sehingga akses berhenti.',
             '<?php\n$_SESSION = [];\nsession_destroy();\nheader("Location: login.php");\n?>'],
        ],
    ],
];
?>

<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Simulasi Alur Fullstack</h1>
        <p>Pahami hubungan <strong>FRONTEND → BACKEND → DATABASE</strong> melalui simulasi langkah demi langkah.</p>
    </div>
    <div class="btn-group">
        <a class="btn btn-accent" href="<?= url('ifp/simulasi.php') ?>" target="_blank">🖥️ Mode Presentasi IFP</a>
    </div>
</div>

<!-- Diagram tiga komponen -->
<div class="card">
    <div class="flow-diagram">
        <div class="flow-node" id="node-frontend">
            <div class="node-icon">🖥️</div>
            <strong>FRONTEND</strong>
            <small>HTML · CSS · JavaScript<br>Browser pengguna</small>
        </div>
        <div class="flow-arrow">⇄</div>
        <div class="flow-node" id="node-backend">
            <div class="node-icon">⚙️</div>
            <strong>BACKEND</strong>
            <small>PHP · Apache<br>Pengolah logika</small>
        </div>
        <div class="flow-arrow">⇄</div>
        <div class="flow-node" id="node-database">
            <div class="node-icon">🗄️</div>
            <strong>DATABASE</strong>
            <small>MySQL · SQL<br>Penyimpanan data</small>
        </div>
    </div>
</div>

<!-- Pilih operasi -->
<div class="card">
    <div class="card-head"><h2>Pilih Proses yang Disimulasikan</h2></div>
    <div class="pill-nav" id="operation-buttons">
        <?php foreach ($simulations as $key => $op): ?>
            <button class="pill" data-op="<?= $key ?>" onclick="pilihOperasi('<?= $key ?>')">
                <?= e($op['label']) ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<!-- Panel simulasi -->
<div class="grid" style="grid-template-columns:1.4fr 1fr;align-items:start">
    <div class="card">
        <div class="card-head">
            <h2 id="step-title">Langkah 1</h2>
            <span class="badge badge-blue" id="step-counter">1 / 8</span>
        </div>
        <div id="step-badge" style="margin-bottom:12px"></div>
        <p id="step-text" style="font-size:1.02rem;min-height:100px"></p>
        <div id="step-progress" class="progress" style="margin-bottom:18px"><span style="width:0%"></span></div>
        <pre class="code-block" id="step-code" style="white-space:pre-wrap"></pre>
        <div class="btn-group">
            <button class="btn btn-light" id="btn-prev" onclick="langkahSebelumnya()">← Kembali</button>
            <button class="btn btn-primary" id="btn-next" onclick="langkahBerikutnya()">Lanjut →</button>
            <button class="btn btn-outline" onclick="ulangiSimulasi()">↻ Ulangi</button>
        </div>
    </div>

    <div class="card">
        <h3>Urutan Langkah</h3>
        <ol class="timeline" id="step-list"></ol>
        <div class="divider"></div>
        <h3>Tips Memahami</h3>
        <ul style="font-size:.88rem">
            <li>Perhatikan warna komponen yang aktif di diagram atas.</li>
            <li>Baca penjelasan sebelum melihat potongan kode.</li>
            <li>Cocokkan kode dengan langkah yang sedang berjalan.</li>
            <li>Gunakan tombol Ulangi untuk mengulang dari awal.</li>
        </ul>
    </div>
</div>

<script>
const simulasi = <?= json_encode($simulations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
let operasiSekarang = 'CREATE';
let langkahSekarang = 0;

function komponenLabel(key) {
    return {frontend: 'FRONTEND', backend: 'BACKEND', database: 'DATABASE'}[key] || key;
}
function komponenWarna(key) {
    return {frontend: 'badge-blue', backend: 'badge-purple', database: 'badge-green'}[key] || 'badge-gray';
}

function pilihOperasi(key) {
    operasiSekarang = key;
    langkahSekarang = 0;
    document.querySelectorAll('#operation-buttons .pill').forEach(function (p) {
        p.classList.toggle('active', p.getAttribute('data-op') === key);
    });
    renderLangkah();
}

function renderLangkah() {
    const op = simulasi[operasiSekarang];
    const steps = op.steps;
    const step = steps[langkahSekarang];

    document.getElementById('step-title').textContent =
        op.label + ' — Langkah ' + (langkahSekarang + 1);
    document.getElementById('step-counter').textContent = (langkahSekarang + 1) + ' / ' + steps.length;
    document.getElementById('step-badge').innerHTML =
        '<span class="badge ' + komponenWarna(step[0]) + '">' + komponenLabel(step[0]) + ' AKTIF</span>';
    document.getElementById('step-text').textContent = step[2];
    document.getElementById('step-code').textContent = step[3];
    document.querySelector('#step-progress > span').style.width =
        ((langkahSekarang + 1) / steps.length * 100) + '%';

    document.getElementById('btn-prev').disabled = langkahSekarang === 0;
    document.getElementById('btn-next').disabled = langkahSekarang === steps.length - 1;

    ['frontend', 'backend', 'database'].forEach(function (id) {
        document.getElementById('node-' + id).classList.toggle('active', id === step[0]);
    });

    const list = document.getElementById('step-list');
    list.innerHTML = '';
    steps.forEach(function (s, i) {
        const li = document.createElement('li');
        li.className = i < langkahSekarang ? 'done' : '';
        li.innerHTML = '<strong>' + (i + 1) + '. ' + s[1] + '</strong>' +
            '<small class="muted">' + komponenLabel(s[0]) + '</small>';
        if (i === langkahSekarang) li.style.borderLeftColor = 'var(--primary)';
        list.appendChild(li);
    });
}

function langkahBerikutnya() {
    const total = simulasi[operasiSekarang].steps.length;
    if (langkahSekarang < total - 1) { langkahSekarang++; renderLangkah(); }
}
function langkahSebelumnya() {
    if (langkahSekarang > 0) { langkahSekarang--; renderLangkah(); }
}
function ulangiSimulasi() { langkahSekarang = 0; renderLangkah(); }

document.addEventListener('DOMContentLoaded', function () {
    const first = document.querySelector('#operation-buttons .pill');
    if (first) first.classList.add('active');
    renderLangkah();

    // Navigasi keyboard: panah kanan/kiri
    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') langkahBerikutnya();
        if (e.key === 'ArrowLeft') langkahSebelumnya();
    });
});
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
