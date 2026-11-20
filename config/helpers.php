<?php
/**
 * helpers.php - Fungsi-fungsi kecil yang dipakai di seluruh aplikasi.
 */

/** Escape output agar aman dari XSS. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Bangun URL internal. Contoh: url('modules/materi/index.php') */
function url(string $path = ''): string
{
    return BASE_PATH . '/' . ltrim($path, '/');
}

/** Bangun URL aset lokal. */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** Alihkan halaman. */
function redirect(string $path): void
{
    header('Location: ' . (preg_match('#^https?://#i', $path) ? $path : url($path)));
    exit;
}

/** Kembali ke halaman sebelumnya (atau fallback). */
function redirect_back(string $fallback = ''): void
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if ($ref !== '' && strpos($ref, BASE_PATH) !== false) {
        header('Location: ' . $ref);
        exit;
    }
    redirect($fallback !== '' ? $fallback : 'index.php');
}

/** Buat/ambil token CSRF. */
function csrf_token(): string
{
    if (empty($_SESSION[CSRF_KEY])) {
        $_SESSION[CSRF_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_KEY];
}

/** Cetak input hidden berisi token CSRF. */
function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Cek token CSRF dari POST. */
function csrf_valid(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && hash_equals($_SESSION[CSRF_KEY] ?? '', $token);
}

/** Hentikan eksekusi bila CSRF tidak valid. */
function csrf_guard(): void
{
    if (!csrf_valid()) {
        http_response_code(403);
        exit('Token CSRF tidak valid. Silakan muat ulang halaman.');
    }
}

/** Simpan pesan sekali pakai (flash message). */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Ambil dan hapus semua pesan flash. */
function get_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Cetak pesan flash dalam bentuk alert Bootstrap-like. */
function render_flash(): void
{
    foreach (get_flash() as $item) {
        $type = in_array($item['type'], ['success', 'danger', 'warning', 'info'], true) ? $item['type'] : 'info';
        echo '<div class="alert alert-' . $type . '">' . e($item['message']) . '</div>';
    }
}

/** Ambil nilai POST dengan aman. */
function post(string $key, $default = '')
{
    return isset($_POST[$key]) && is_scalar($_POST[$key]) ? $_POST[$key] : $default;
}

/** Ambil nilai GET dengan aman. */
function query(string $key, $default = '')
{
    return isset($_GET[$key]) && is_scalar($_GET[$key]) ? $_GET[$key] : $default;
}

/** Nilai lama untuk mengisi kembali form setelah validasi gagal. */
function old(string $key, $default = '')
{
    return isset($_SESSION['old'][$key]) ? e($_SESSION['old'][$key]) : e($default);
}

/** Simpan seluruh nilai POST untuk dipakai old(). */
function remember_post(): void
{
    $_SESSION['old'] = [];
    foreach ($_POST as $key => $value) {
        if ($key === 'csrf_token' || $key === 'password' || is_array($value)) {
            continue;
        }
        $_SESSION['old'][$key] = $value;
    }
}

/** Hapus nilai old. */
function clear_old(): void
{
    unset($_SESSION['old']);
}

/** Batasi panjang teks untuk tampilan ringkas. */
function str_limit(?string $text, int $limit = 100, string $end = '…'): string
{
    $text = trim((string) $text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return mb_substr($text, 0, $limit) . $end;
}

/** Format tanggal Indonesia. */
function format_date(?string $date, bool $withTime = false): string
{
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    $ts = strtotime($date);
    if ($ts === false) {
        return e($date);
    }
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        7 => 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $result = date('d', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    if ($withTime) {
        $result .= ' ' . date('H:i', $ts) . ' WIB';
    }
    return $result;
}

/** Format angka desimal untuk nilai. */
function fmt_score($value, int $digits = 2): string
{
    return number_format((float) $value, $digits, ',', '.');
}

/** Predikat kelulusan kompetensi. */
function score_predicate(float $score): string
{
    if ($score >= 85) return 'Sangat Baik';
    if ($score >= 75) return 'Baik';
    if ($score >= 65) return 'Cukup';
    return 'Perlu Remediasi';
}

/** Kelas warna untuk predikat. */
function score_badge_class(float $score): string
{
    if ($score >= 85) return 'badge-green';
    if ($score >= 75) return 'badge-blue';
    if ($score >= 65) return 'badge-yellow';
    return 'badge-red';
}

/** Hitung nilai kompetensi dari POST berdasarkan bobot. */
function compute_weighted_score(array $post, array $weights): array
{
    $result = [];
    $total = 0.0;
    foreach ($weights as $field => $weight) {
        $raw = max(1, min(4, (int) ($post[$field] ?? 1)));
        $result[$field] = $raw;
        $total += ($raw / 4) * $weight;
    }
    $result['total_score'] = round($total, 2);
    return $result;
}

/** Hitung nilai seminar dari POST berdasarkan bobot (skala 0-100). */
function compute_seminar_score(array $post): array
{
    $result = [];
    $total = 0.0;
    foreach (SEMINAR_WEIGHTS as $field => $weight) {
        $raw = max(0, min(100, (int) ($post[$field] ?? 0)));
        $result[$field] = $raw;
        $total += ($raw / 100) * $weight;
    }
    $result['total_score'] = round($total, 2);
    return $result;
}

/** Bersihkan nama file uploaded. */
function safe_filename(string $name): string
{
    $name = basename($name);
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
    return $name ?: ('file_' . date('YmdHis'));
}

/** Cek apakah request dikirim dengan metode POST. */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Ambil IP pengguna (untuk log sederhana). */
function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}
