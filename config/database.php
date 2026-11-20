<?php
/**
 * database.php - Koneksi PDO ke MySQL lokal.
 * Menggunakan prepared statement di seluruh aplikasi.
 */

require_once __DIR__ . '/config.php';

/**
 * Ambil instance PDO (singleton).
 *
 * @return PDO
 */
function db()
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Jangan bocorkan detail koneksi ke pengguna akhir.
        http_response_code(500);
        echo '<!doctype html><html lang="id"><meta charset="utf-8">';
        echo '<title>Koneksi Database Gagal</title>';
        echo '<body style="font-family:Arial,sans-serif;padding:40px;line-height:1.7">';
        echo '<h1>Koneksi database gagal</h1>';
        echo '<p>Periksa kembali:</p><ul>';
        echo '<li>Laragon (Apache &amp; MySQL) sudah berjalan.</li>';
        echo '<li>Database <strong>' . htmlspecialchars(DB_NAME) . '</strong> sudah dibuat.</li>';
        echo '<li>File <code>database/database.sql</code> sudah di-import.</li>';
        echo '<li>Pengaturan DB di <code>config/config.php</code> sesuai.</li>';
        echo '</ul><p>Detail teknis: <code>' . htmlspecialchars($e->getMessage()) . '</code></p>';
        echo '</body></html>';
        exit;
    }

    return $pdo;
}

/**
 * Jalankan query SELECT sederhana.
 */
function db_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Ambil satu baris.
 */
function db_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/**
 * Jalankan INSERT/UPDATE/DELETE, kembalikan jumlah baris.
 */
function db_run(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Ambil nilai tunggal (scalar).
 */
function db_value(string $sql, array $params = [])
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

/**
 * Ambil nilai dari tabel settings.
 */
function setting(string $key, string $default = ''): string
{
    $row = db_one('SELECT setting_value FROM settings WHERE setting_key = ?', [$key]);
    return $row ? (string) $row['setting_value'] : $default;
}
