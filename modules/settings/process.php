<?php
/** modules/settings/process.php - Simpan pengaturan & kelola akun. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();

$action = post('action', query('action', ''));

if ($action === 'delete') {
    $id = (int) query('id', 0);
    if ($id === (int) current_user()['id']) {
        flash('danger', 'Tidak dapat menghapus akun sendiri.');
    } else {
        db_run('DELETE FROM users WHERE id = ?', [$id]);
        db_run('INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?,?,?,?)',
            [(int) current_user()['id'], 'delete_user', 'Menghapus akun #' . $id, client_ip()]);
        flash('success', 'Akun berhasil dihapus.');
    }
    redirect('modules/settings/index.php');
}

if (!is_post()) redirect('modules/settings/index.php');
csrf_guard();

if ($action === 'settings') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0 && is_scalar($value)) {
            $settingKey = substr($key, 8);
            db_run('UPDATE settings SET setting_value = ? WHERE setting_key = ?', [trim((string)$value), $settingKey]);
        }
    }
    db_run('INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?,?,?,?)',
        [(int) current_user()['id'], 'update_settings', 'Memperbarui pengaturan aplikasi', client_ip()]);
    flash('success', 'Pengaturan berhasil disimpan.');
    redirect('modules/settings/index.php');
}

if ($action === 'save_user') {
    $id = (int) post('id', 0);
    $username = trim(post('username'));
    $fullName = trim(post('full_name'));
    $role = in_array(post('role'), ['admin','guru','siswa'], true) ? post('role') : 'guru';
    $studentId = (int) post('student_id', 0) ?: null;
    $isActive = post('is_active') ? 1 : 0;
    $password = post('password');

    if ($username === '' || $fullName === '') {
        flash('danger', 'Username dan nama lengkap wajib diisi.');
        redirect('modules/settings/form.php' . ($id ? '?id=' . $id : ''));
    }

    $exists = db_one('SELECT id FROM users WHERE username = ?' . ($id ? ' AND id <> ?' : ''),
        $id ? [$username, $id] : [$username]);
    if ($exists) {
        flash('danger', 'Username sudah digunakan.');
        redirect('modules/settings/form.php' . ($id ? '?id=' . $id : ''));
    }

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        if ($id) {
            db_run('UPDATE users SET username = ?, full_name = ?, role = ?, student_id = ?, is_active = ?, password = ? WHERE id = ?',
                [$username, $fullName, $role, $studentId, $isActive, $hash, $id]);
        } else {
            db_run('INSERT INTO users (username, password, full_name, role, student_id, is_active) VALUES (?,?,?,?,?,?)',
                [$username, $hash, $fullName, $role, $studentId, $isActive]);
        }
    } else {
        if ($id) {
            db_run('UPDATE users SET username = ?, full_name = ?, role = ?, student_id = ?, is_active = ? WHERE id = ?',
                [$username, $fullName, $role, $studentId, $isActive, $id]);
        } else {
            flash('danger', 'Password wajib diisi untuk akun baru.');
            redirect('modules/settings/form.php');
        }
    }

    db_run('INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?,?,?,?)',
        [(int) current_user()['id'], 'save_user', 'Menyimpan akun ' . $username, client_ip()]);
    flash('success', 'Akun berhasil disimpan.');
    redirect('modules/settings/index.php');
}

flash('danger', 'Aksi tidak dikenal.');
redirect('modules/settings/index.php');
