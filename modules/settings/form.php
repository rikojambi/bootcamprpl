<?php
/** modules/settings/form.php - Tambah/edit akun guru/admin/siswa. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();
$id = (int) query('id', 0);
$user = $id ? db_one('SELECT * FROM users WHERE id = ?', [$id]) : null;
$page_title = $user ? 'Edit Akun' : 'Tambah Akun';
$students = db_all('SELECT id, nis, name, class_name FROM students ORDER BY name');
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <p style="margin:0 0 6px"><a href="<?= url('modules/settings/index.php') ?>">← Pengaturan</a></p>
        <h1><?= e($page_title) ?></h1>
    </div>
</div>

<div class="card" style="max-width:860px">
    <form method="post" action="<?= url('modules/settings/process.php') ?>">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="save_user">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="form-grid">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?= e($user['username'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="full_name">Nama Lengkap</label>
                <input type="text" name="full_name" id="full_name" value="<?= e($user['full_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role">
                    <option value="guru" <?= ($user['role'] ?? 'guru') === 'guru' ? 'selected' : '' ?>>Guru</option>
                    <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="siswa" <?= ($user['role'] ?? '') === 'siswa' ? 'selected' : '' ?>>Siswa</option>
                </select>
            </div>
            <div class="form-group">
                <label for="student_id">Siswa Terkait (khusus role siswa)</label>
                <select name="student_id" id="student_id">
                    <option value="">- Tidak ada -</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= ($user['student_id'] ?? 0) == $s['id'] ? 'selected' : '' ?>>
                            <?= e($s['nis'] . ' - ' . $s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="password">Password <?= $user ? '(kosongkan bila tidak diubah)' : '' ?></label>
                <input type="password" name="password" id="password" <?= $user ? '' : 'required' ?>>
                <span class="hint">Minimal 6 karakter. Disimpan sebagai hash bcrypt.</span>
            </div>
            <div class="form-group">
                <label for="is_active">Status Akun</label>
                <select name="is_active" id="is_active">
                    <option value="1" <?= ($user['is_active'] ?? 1) ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= $user && !$user['is_active'] ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">💾 Simpan Akun</button>
            <a class="btn btn-light" href="<?= url('modules/settings/index.php') ?>">Batal</a>
        </div>
    </form>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
