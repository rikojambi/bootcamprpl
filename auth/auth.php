<?php
/**
 * auth.php - Autentikasi berbasis session.
 * Role: admin/guru = pengelola, siswa = peserta.
 */

require_once __DIR__ . '/../config/database.php';

/** Ambil data user yang sedang login (null bila belum login). */
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = db_one(
        'SELECT u.id, u.username, u.full_name, u.role, u.student_id, u.is_active,
                s.name AS student_name, s.class_name
         FROM users u
         LEFT JOIN students s ON s.id = u.student_id
         WHERE u.id = ? AND u.is_active = 1',
        [(int) $_SESSION['user_id']]
    );
    if (!$cache) {
        session_destroy();
        $cache = null;
    }
    return $cache;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function user_role(): string
{
    $u = current_user();
    return $u ? $u['role'] : '';
}

/** Guru/admin boleh mengelola seluruh modul. */
function can_manage(): bool
{
    return in_array(user_role(), ['admin', 'guru'], true);
}

function is_admin(): bool
{
    return user_role() === 'admin';
}

/** Paksa login sebelum mengakses halaman. */
function require_login(): void
{
    if (!is_logged_in()) {
        flash('warning', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}

/** Paksa role tertentu. */
function require_role(string ...$roles): void
{
    require_login();
    if (!in_array(user_role(), $roles, true)) {
        http_response_code(403);
        include APP_ROOT . '/includes/header.php';
        echo '<div class="card"><h1>Akses ditolak</h1><p>Anda tidak memiliki izin untuk membuka halaman ini.</p></div>';
        include APP_ROOT . '/includes/footer.php';
        exit;
    }
}

/** Paksa role guru/admin. */
function require_manager(): void
{
    require_role('admin', 'guru');
}

/**
 * Proses percobaan login.
 *
 * @return array [bool $sukses, string $pesan]
 */
function attempt_login(string $username, string $password): array
{
    $username = trim($username);
    if ($username === '' || $password === '') {
        return [false, 'Username dan password wajib diisi.'];
    }

    // Batasi percobaan gagal sederhana (brute-force protection)
    if (empty($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    if (($_SESSION['login_lock_until'] ?? 0) > time()) {
        $sisa = (int) $_SESSION['login_lock_until'] - time();
        return [false, 'Terlalu banyak percobaan. Coba lagi dalam ' . $sisa . ' detik.'];
    }

    $user = db_one('SELECT * FROM users WHERE username = ? LIMIT 1', [$username]);

    if (!$user || !password_verify($password, $user['password']) || !(int) $user['is_active']) {
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['login_lock_until'] = time() + 60;
            $_SESSION['login_attempts'] = 0;
            return [false, 'Akun sementara dikunci selama 60 detik.'];
        }
        return [false, 'Username atau password salah.'];
    }

    // Login berhasil
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);
    db_run('UPDATE users SET last_login = NOW() WHERE id = ?', [(int) $user['id']]);

    return [true, 'Selamat datang, ' . $user['full_name'] . '.'];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Daftar role dalam bentuk label. */
function role_label(string $role): string
{
    return [
        'admin' => 'Admin',
        'guru'  => 'Guru',
        'siswa' => 'Siswa',
    ][$role] ?? $role;
}
