<?php
/** modules/reports/import.php - Impor data siswa dari CSV lokal. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();
$page_title = 'Impor Data Siswa (CSV)';
$result = null;

if (is_post()) {
    csrf_guard();
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $result = ['type' => 'danger', 'message' => 'Pilih file CSV terlebih dahulu.'];
    } else {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if ($handle) {
            // Lewati BOM di baris pertama
            $header = fgetcsv($handle);
            if ($header) $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            $expected = ['nis','nama','kelas','jenis_kelamin'];
            if (!$header || count(array_intersect($expected, array_map('strtolower', array_map('trim', $header)))) < 2) {
                $result = ['type' => 'danger', 'message' => 'Format header CSV tidak dikenal. Gunakan: nis,nama,kelas,jenis_kelamin'];
            } else {
                $inserted = 0; $updated = 0; $failed = 0;
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) < 2) continue;
                    $nis = trim($row[0]);
                    $name = trim($row[1] ?? '');
                    $class = trim($row[2] ?? 'XII PPLG 1');
                    $gender = strtoupper(trim($row[3] ?? 'L'));
                    if ($nis === '' || $name === '') { $failed++; continue; }
                    if (!in_array($gender, ['L','P'], true)) $gender = 'L';
                    $existing = db_one('SELECT id FROM students WHERE nis = ?', [$nis]);
                    if ($existing) {
                        db_run('UPDATE students SET name = ?, class_name = ?, gender = ? WHERE id = ?',
                            [$name, $class, $gender, (int)$existing['id']]);
                        $updated++;
                    } else {
                        db_run('INSERT INTO students (nis, name, class_name, gender) VALUES (?,?,?,?)',
                            [$nis, $name, $class, $gender]);
                        $inserted++;
                    }
                }
                fclose($handle);
                db_run('INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?,?,?,?)',
                    [(int) current_user()['id'], 'import_csv',
                     "Import CSV: $inserted baru, $updated diperbarui, $failed gagal", client_ip()]);
                $result = [
                    'type' => 'success',
                    'message' => "Impor selesai: $inserted siswa ditambahkan, $updated diperbarui, $failed gagal.",
                ];
            }
        } else {
            $result = ['type' => 'danger', 'message' => 'Gagal membaca file CSV.'];
        }
    }
}
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <p style="margin:0 0 6px"><a href="<?= url('modules/reports/index.php') ?>">← Laporan</a></p>
        <h1>Impor Data Siswa (CSV)</h1>
        <p>File CSV diproses lokal di server Laragon. Tidak ada data yang dikirim ke internet.</p>
    </div>
</div>

<?php if ($result): ?>
    <div class="alert alert-<?= $result['type'] ?>"><?= e($result['message']) ?></div>
<?php endif; ?>

<div class="grid grid-2">
    <div class="card">
        <h2>Unggah File CSV</h2>
        <form method="post" enctype="multipart/form-data">
            <?php csrf_field(); ?>
            <div class="form-group">
                <label for="csv_file">File CSV</label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv,text/csv" required>
                <span class="hint">Ukuran maksimal 2 MB. Gunakan header: nis,nama,kelas,jenis_kelamin</span>
            </div>
            <button class="btn btn-primary" type="submit">⬆️ Impor Sekarang</button>
        </form>
        <div class="divider"></div>
        <h3>Contoh Format</h3>
        <pre class="code-block" style="white-space:pre-wrap">nis,nama,kelas,jenis_kelamin
2023013,Siswa Baru,XII PPLG 1,L
2023014,Siswi Baru,XII PPLG 1,P</pre>
        <a class="btn btn-light btn-sm" href="<?= url('modules/reports/contoh.csv') ?>">⬇️ Unduh Contoh CSV</a>
    </div>

    <div class="card">
        <h2>Catatan Penting</h2>
        <ul style="padding-left:22px">
            <li>Siswa dengan NIS yang sama akan diperbarui datanya.</li>
            <li>NIS baru akan ditambahkan sebagai siswa aktif.</li>
            <li>Kolom <code>jenis_kelamin</code> diisi <code>L</code> atau <code>P</code>.</li>
            <li>Password akun siswa tidak dibuat otomatis; buat manual melalui pengaturan jika diperlukan.</li>
            <li>Data tetap berjalan offline di komputer lokal.</li>
        </ul>
        <a class="btn btn-outline" href="<?= url('modules/reports/export.php') ?>">⬇️ Ekspor Data Siswa</a>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
