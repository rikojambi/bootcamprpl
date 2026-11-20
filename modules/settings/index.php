<?php
/** modules/settings/index.php - Pengaturan aplikasi, akun, dan backup. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();
$page_title = 'Pengaturan';

$settings = db_all('SELECT * FROM settings ORDER BY id');
$users = db_all('SELECT u.*, s.name AS student_name FROM users u LEFT JOIN students s ON s.id = u.student_id ORDER BY u.role, u.username');
$logs = db_all('SELECT a.*, u.full_name AS user_name FROM activity_log a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 20');
$currentUser = current_user();
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Pengaturan Aplikasi</h1>
        <p>Konfigurasi lokal, kelola akun guru/admin, dan cadangkan database. Semua berjalan offline di Laragon.</p>
    </div>
</div>

<div class="tabs">
    <button class="tab active" data-tab="umum">1. Pengaturan Umum</button>
    <button class="tab" data-tab="akun">2. Kelola Akun</button>
    <button class="tab" data-tab="backup">3. Backup & Database</button>
    <button class="tab" data-tab="log">4. Log Aktivitas</button>
</div>

<div class="tab-container">
<section class="tab-panel active" data-panel="umum">
    <div class="card">
        <div class="card-head"><h2>Pengaturan Umum</h2><span class="badge badge-blue">Tersimpan di database lokal</span></div>
        <form method="post" action="<?= url('modules/settings/process.php') ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="settings">
            <div class="form-grid">
                <?php foreach ($settings as $s): ?>
                    <div class="form-group">
                        <label><?= e($s['description'] ?: $s['setting_key']) ?></label>
                        <input type="text" name="setting_<?= e($s['setting_key']) ?>" value="<?= e($s['setting_value']) ?>">
                        <span class="hint">Kunci: <code><?= e($s['setting_key']) ?></code></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-primary" type="submit">💾 Simpan Pengaturan</button>
        </form>
    </div>
</section>

<section class="tab-panel" data-panel="akun">
    <div class="card">
        <div class="card-head"><h2>Daftar Akun</h2><span class="badge badge-blue"><?= count($users) ?> akun</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Siswa Terkait</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= e($u['username']) ?></strong></td>
                        <td><?= e($u['full_name']) ?></td>
                        <td><span class="badge badge-<?= $u['role'] === 'siswa' ? 'blue' : 'purple' ?>"><?= e(ucfirst($u['role'])) ?></span></td>
                        <td><?= e($u['student_name'] ?: '-') ?></td>
                        <td><span class="badge badge-<?= $u['is_active'] ? 'green' : 'gray' ?>"><?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                        <td class="actions">
                            <a class="btn btn-light btn-sm" href="<?= url('modules/settings/form.php?id=' . (int)$u['id']) ?>">Edit</a>
                            <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                            <a class="btn btn-danger btn-sm" data-confirm="Hapus akun ini?" href="<?= url('modules/settings/process.php?action=delete&id=' . (int)$u['id']) ?>">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a class="btn btn-primary" style="margin-top:16px" href="<?= url('modules/settings/form.php') ?>">＋ Tambah Akun</a>
    </div>
</section>

<section class="tab-panel" data-panel="backup">
    <div class="grid grid-2">
        <div class="card">
            <h2>Cadangkan Database</h2>
            <p>Unduh salinan lengkap database <code>bootcamp_fullstack</code> dalam format SQL. Simpan di folder <code>storage/backups</code> atau lokasi lain di komputer Anda.</p>
            <a class="btn btn-accent btn-lg" href="<?= url('modules/settings/backup.php') ?>">💾 Unduh Backup SQL</a>
            <div class="divider"></div>
            <h3>Informasi Sistem</h3>
            <table class="data-table">
                <tr><th>PHP</th><td><?= e(PHP_VERSION) ?></td></tr>
                <tr><th>MySQL</th><td><?= e(db_value('SELECT VERSION()')) ?></td></tr>
                <tr><th>Database</th><td><?= e(DB_NAME) ?></td></tr>
                <tr><th>Web Server</th><td><?= e($_SERVER['SERVER_SOFTWARE'] ?? 'PHP Built-in') ?></td></tr>
                <tr><th>Versi Aplikasi</th><td><?= e(APP_VERSION) ?></td></tr>
                <tr><th>Path Aplikasi</th><td><code><?= e(APP_ROOT) ?></code></td></tr>
            </table>
        </div>
        <div class="card">
            <h2>Cara Memulihkan (Restore)</h2>
            <ol style="padding-left:22px">
                <li>Buka Laragon, pastikan MySQL berjalan.</li>
                <li>Buka <code>http://localhost/phpmyadmin</code>.</li>
                <li>Pilih database <code>bootcamp_fullstack</code>.</li>
                <li>Klik tab <strong>Import</strong>, pilih file backup SQL.</li>
                <li>Klik <strong>Go</strong> dan tunggu hingga selesai.</li>
            </ol>
            <div class="alert alert-info" style="font-size:.85rem">
                Alternatif tanpa phpMyAdmin: jalankan <code>backup\restore.bat</code> dari folder proyek.
            </div>
            <h3>File Penting</h3>
            <ul style="padding-left:22px;font-size:.88rem">
                <li><code>database/database.sql</code> — schema + data awal</li>
                <li><code>install.bat</code> — pasang junction Laragon</li>
                <li><code>start-server.bat</code> — jalankan server lokal</li>
                <li><code>backup.bat</code> — cadangkan database otomatis</li>
            </ul>
        </div>
    </div>
</section>

<section class="tab-panel" data-panel="log">
    <div class="card">
        <div class="card-head"><h3>Aktivitas Terbaru</h3></div>
        <?php if (!$logs): ?><p class="muted">Belum ada aktivitas tercatat.</p><?php endif; ?>
        <?php foreach ($logs as $l): ?>
            <div style="padding:8px 0;border-bottom:1px solid var(--line);font-size:.85rem">
                <strong><?= e($l['action']) ?></strong> — <?= e($l['description']) ?>
                <br><small class="muted"><?= e($l['user_name'] ?? 'Sistem') ?> · <?= e(format_date($l['created_at'], true)) ?> · <?= e($l['ip_address']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</section>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
