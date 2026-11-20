<?php
/** ifp/simulasi.php - Presentasi simulasi FRONTEND → BACKEND → DATABASE di IFP. */
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/auth/auth.php';
require_once __DIR__ . '/_layout.php';

require_login();

$ops = [
    'CREATE' => [
        'label' => 'Tambah Data (CREATE)',
        'steps' => [
            ['FRONTEND', 'Pengguna mengisi form', 'Siswa mengisi nama dan kelas di browser, lalu menekan tombol Simpan.', '<form method="post" action="simpan.php">\n  <input name="nama">\n  <input name="kelas">\n  <button>Simpan</button>\n</form>'],
            ['BACKEND', 'PHP menerima POST', 'PHP membaca data dari $_POST lalu memeriksa isinya.', '$nama = $_POST["nama"];\n$kelas = $_POST["kelas"];'],
            ['BACKEND', 'Query disiapkan', 'Prepared statement mencegah SQL injection.', '$stmt = $pdo->prepare(\n  "INSERT INTO siswa (nama,kelas) VALUES (?,?)"\n);\n$stmt->execute([$nama,$kelas]);'],
            ['DATABASE', 'MySQL menyimpan', 'Database membuat satu baris baru dan mengembalikan status sukses.', 'INSERT INTO siswa (nama,kelas)\nVALUES ("Ahmad","XII PPLG 1");\n-- 1 row affected'],
            ['FRONTEND', 'Hasil tampil', 'Browser menampilkan tabel siswa dengan data baru.', '<tr><td>Ahmad</td><td>XII PPLG 1</td></tr>'],
        ],
    ],
    'READ' => [
        'label' => 'Lihat Data (READ)',
        'steps' => [
            ['FRONTEND', 'Buka halaman daftar', 'Browser mengirim GET ke server tanpa data formulir.', 'GET /index.php HTTP/1.1'],
            ['BACKEND', 'Query SELECT', 'PHP mengambil seluruh baris siswa.', '$data = $pdo->query(\n  "SELECT * FROM siswa ORDER BY id DESC"\n)->fetchAll();'],
            ['DATABASE', 'MySQL mengembalikan baris', 'Database membaca tabel dan mengirim kumpulan baris ke PHP.', '+----+---------+\n| id | nama    |\n+----+---------+\n| 2  | Siti    |\n| 1  | Ahmad   |\n+----+---------+'],
            ['BACKEND', 'PHP buat HTML', 'PHP mengulang hasil query dengan foreach.', '<?php foreach ($data as $row): ?>\n  <td><?= htmlspecialchars($row["nama"]) ?></td>\n<?php endforeach; ?>'],
            ['FRONTEND', 'Tabel tampil', 'Siswa melihat data asli dari database.', '<table><tr><td>Siti</td></tr></table>'],
        ],
    ],
    'UPDATE' => [
        'label' => 'Ubah Data (UPDATE)',
        'steps' => [
            ['FRONTEND', 'Tombol Edit', 'Browser mengirim id siswa melalui URL.', '<a href="edit.php?id=1">Edit</a>'],
            ['BACKEND', 'Ambil data lama', 'PHP menjalankan SELECT berdasarkan id.', '$stmt = $pdo->prepare("SELECT * FROM siswa WHERE id=?");\n$stmt->execute([$id]);'],
            ['FRONTEND', 'Form terisi', 'Browser menampilkan form dengan nilai lama.', '<input name="nama" value="<?=$row["nama"]?>">'],
            ['BACKEND', 'Jalankan UPDATE', 'Klausa WHERE wajib ada agar hanya satu baris berubah.', '$stmt = $pdo->prepare(\n  "UPDATE siswa SET nama=? WHERE id=?"\n);\n$stmt->execute([$nama,$id]);'],
            ['DATABASE', 'Baris diperbarui', 'MySQL mengganti nilai baris yang sesuai.', 'UPDATE siswa SET nama="Siti N." WHERE id=1;\n-- 1 row affected'],
        ],
    ],
    'DELETE' => [
        'label' => 'Hapus Data (DELETE)',
        'steps' => [
            ['FRONTEND', 'Konfirmasi', 'Browser menampilkan dialog konfirmasi sebelum menghapus.', '<button onclick="return confirm(\'Yakin?\')">Hapus</button>'],
            ['BACKEND', 'Terima id', 'PHP memastikan id berupa angka.', '$id = (int) $_POST["id"];'],
            ['BACKEND', 'Query DELETE', 'Gunakan WHERE agar tidak menghapus seluruh tabel.', '$stmt = $pdo->prepare("DELETE FROM siswa WHERE id=?");\n$stmt->execute([$id]);'],
            ['DATABASE', 'Baris terhapus', 'MySQL menghapus tepat satu baris.', 'DELETE FROM siswa WHERE id=1;\n-- 1 row affected'],
            ['FRONTEND', 'Daftar diperbarui', 'Browser menampilkan daftar tanpa baris yang dihapus.', '<div class="alert">Data berhasil dihapus</div>'],
        ],
    ],
    'LOGIN' => [
        'label' => 'Login & Session',
        'steps' => [
            ['FRONTEND', 'Form login', 'Username dan password dikirim via POST.', '<input name="username">\n<input type="password" name="password">'],
            ['BACKEND', 'Cari user', 'PHP menyiapkan query berdasarkan username.', '$stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");'],
            ['DATABASE', 'Kembalikan hash', 'Database mengembalikan hash password, bukan password asli.', 'SELECT id,username,password FROM users WHERE username="guru";'],
            ['BACKEND', 'Verifikasi', 'password_verify membandingkan input dengan hash.', 'if (password_verify($password,$user["password"])) { ... }'],
            ['BACKEND', 'Buat session', 'Session ID diperbarui untuk keamanan.', 'session_regenerate_id(true);\n$_SESSION["user_id"]=$user["id"];'],
            ['FRONTEND', 'Dashboard terbuka', 'Halaman terlindungi hanya bisa dibuka bila session ada.', 'if (empty($_SESSION["user_id"])) header("Location: login.php");'],
        ],
    ],
];

$opKey = query('op', 'CREATE');
if (!isset($ops[$opKey])) $opKey = 'CREATE';
$step = max(0, min(count($ops[$opKey]['steps']) - 1, (int) query('step', 0)));
$current = $ops[$opKey]['steps'][$step];

ifp_header('SIMULASI ARSITEKTUR FULLSTACK', 'FRONTEND → BACKEND → DATABASE · Offline Lokal');
?>

<main class="ifp-body wide">
    <div class="ifp-grid" style="grid-template-columns:repeat(5,1fr);margin-bottom:22px">
        <?php foreach ($ops as $key => $op): ?>
            <a class="ifp-btn <?= $key === $opKey ? 'primary' : '' ?>" style="width:100%"
               href="<?= url('ifp/simulasi.php?op=' . $key) ?>"><?= e($op['label']) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="ifp-grid cols-3" style="align-items:center;margin-bottom:22px">
        <?php foreach (['FRONTEND','BACKEND','DATABASE'] as $comp): ?>
            <div class="ifp-step-box text-center" style="min-height:auto;<?= $current[0] === $comp ? 'border-color:var(--ifp-primary);background:#eef4ff' : '' ?>">
                <div style="font-size:2.4rem"><?= ['FRONTEND'=>'🖥️','BACKEND'=>'⚙️','DATABASE'=>'🗄️'][$comp] ?></div>
                <strong><?= $comp ?></strong>
                <?php if ($current[0] === $comp): ?><span class="ifp-badge green">AKTIF</span><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="ifp-slide" id="ifp-slide">
        <span class="ifp-badge">LANGKAH <?= $step + 1 ?> / <?= count($ops[$opKey]['steps']) ?></span>
        <h2><?= e($current[1]) ?></h2>
        <p><strong><?= e($current[0]) ?></strong></p>
        <p><?= e($current[2]) ?></p>
        <pre><?= e($current[3]) ?></pre>
    </div>

    <div style="display:flex;justify-content:space-between;gap:14px;margin-top:22px;flex-wrap:wrap">
        <a class="ifp-btn" href="<?= url('ifp/index.php') ?>">🏠 Beranda</a>
        <div class="btn-group">
            <?php if ($step > 0): ?>
                <a class="ifp-btn" id="prev-slide" href="<?= url('ifp/simulasi.php?op=' . $opKey . '&step=' . ($step - 1)) ?>">← Kembali</a>
            <?php endif; ?>
            <?php if ($step < count($ops[$opKey]['steps']) - 1): ?>
                <a class="ifp-btn accent" id="next-slide" href="<?= url('ifp/simulasi.php?op=' . $opKey . '&step=' . ($step + 1)) ?>">Lanjut →</a>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php ifp_footer('<strong>' . e($ops[$opKey]['label']) . '</strong> · Langkah ' . ($step + 1)); ?>
