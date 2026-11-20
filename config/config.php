<?php
/**
 * config.php - Konfigurasi utama aplikasi.
 * Semua pengaturan lokal disimpan di sini agar mudah dipelajari siswa.
 */

// Zona waktu Indonesia
date_default_timezone_set('Asia/Jakarta');

// Informasi aplikasi
define('APP_NAME', 'BOOTCAMP PRE-UKK FULLSTACK DEVELOPER');
define('APP_SHORT', 'Bootcamp Pre-UKK');
define('APP_VERSION', '1.0.0');
define('SCHOOL_NAME', 'SMKN 6 Muaro Jambi');
define('PROGRAM_NAME', 'PPLG - Kelas XII');
define('AUTHOR_NAME', 'Riko Hardona, S.Pd.');

/**
 * Path aplikasi di web server.
 * Laragon: C:/laragon/www/bootcamp  ->  /bootcamp
 * Ubah nilai ini bila folder dipasang dengan nama berbeda.
 */
define('BASE_PATH', '/bootcamp');

// Koneksi database MySQL lokal (Laragon)
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'bootcamp_fullstack');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Path filesystem
define('APP_ROOT', dirname(__DIR__));
define('STORAGE_PATH', APP_ROOT . '/storage');
define('UPLOAD_PATH', STORAGE_PATH . '/uploads');
define('BACKUP_PATH', STORAGE_PATH . '/backups');

// Keamanan session
define('SESSION_NAME', 'BOOTCAMPSESS');
define('CSRF_KEY', 'bootcamp_csrf_token');

// Setelan tampilan
define('DEFAULT_FONT_SIZE', 18);

// Daftar bobot penilaian kompetensi (harus berjumlah 100)
define('COMPETENCY_WEIGHTS', [
    'score_understanding'    => 10,
    'score_trace'            => 15,
    'score_implementation'   => 30,
    'score_db_security'      => 15,
    'score_testing'          => 20,
    'score_communication'    => 10,
]);

// Label setiap aspek kompetensi
define('COMPETENCY_LABELS', [
    'score_understanding'  => 'Memahami Tugas',
    'score_trace'          => 'Menelusuri Kode',
    'score_implementation' => 'Implementasi',
    'score_db_security'    => 'Database & Keamanan',
    'score_testing'        => 'Testing & Debugging',
    'score_communication'  => 'Komunikasi Teknis',
]);

// Bobot penilaian seminar PKL
define('SEMINAR_WEIGHTS', [
    'problem_understanding' => 10,
    'architecture_db'       => 15,
    'code_understanding'    => 20,
    'live_coding'           => 30,
    'testing'               => 15,
    'communication'         => 10,
]);

define('SEMINAR_LABELS', [
    'problem_understanding' => 'Pemahaman Masalah',
    'architecture_db'       => 'Arsitektur & Database',
    'code_understanding'    => 'Pemahaman Kode',
    'live_coding'           => 'Live Coding',
    'testing'               => 'Pengujian',
    'communication'         => 'Komunikasi',
]);

// Kategori bank pertanyaan bedah kode
define('QUESTION_CATEGORIES', [
    'HTML', 'CSS', 'JavaScript', 'PHP', 'MySQL',
    'API', 'Authentication', 'Debugging', 'Security',
]);

// Level tantangan live coding
define('CHALLENGE_LEVELS', [
    'beginner'     => 'Level 1 - Beginner',
    'intermediate' => 'Level 2 - Intermediate',
    'advanced'     => 'Level 3 - Advanced',
]);

// Opsi skala penilaian 1-4
define('SCALE_OPTIONS', [
    1 => '1 - Perlu bimbingan intensif',
    2 => '2 - Mulai berkembang',
    3 => '3 - Kompeten',
    4 => '4 - Sangat baik',
]);

// Error reporting: tampilkan saat pengembangan, sembunyikan di produksi
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT . '/storage/error.log');

// Session hanya aktif bila belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

require_once __DIR__ . '/helpers.php';
