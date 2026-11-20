<?php
/** modules/settings/backup.php - Unduh cadangan database lengkap (SQL). */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();

/** Escape nilai untuk SQL dump. */
function sql_quote($pdo, $value): string
{
    if ($value === null) return 'NULL';
    return $pdo->quote((string) $value);
}

$pdo = db();
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

$sql = "-- ============================================================\n";
$sql .= "-- BACKUP DATABASE " . DB_NAME . "\n";
$sql .= "-- Dibuat: " . date('Y-m-d H:i:s') . " WIB\n";
$sql .= "-- Aplikasi: " . APP_NAME . " v" . APP_VERSION . "\n";
$sql .= "-- ============================================================\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tables as $table) {
    $create = $pdo->query('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`')->fetch(PDO::FETCH_NUM);
    $sql .= "-- ------------------------------------------------------------\n";
    $sql .= "-- Tabel: {$table}\n";
    $sql .= "-- ------------------------------------------------------------\n";
    $sql .= "DROP TABLE IF EXISTS `" . $table . "`;\n";
    $sql .= $create[1] . ";\n\n";

    $rows = $pdo->query('SELECT * FROM `' . str_replace('`', '``', $table) . '`')->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        $columns = array_keys($rows[0]);
        $colList = '`' . implode('`, `', $columns) . '`';
        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $col) {
                $values[] = sql_quote($pdo, $row[$col]);
            }
            $sql .= "INSERT INTO `" . $table . "` ($colList) VALUES (" . implode(', ', $values) . ");\n";
        }
        $sql .= "\n";
    }
}

$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

// Simpan juga ke storage/backups
$backupDir = BACKUP_PATH;
if (!is_dir($backupDir)) @mkdir($backupDir, 0775, true);
$filename = 'backup_' . DB_NAME . '_' . date('Ymd_His') . '.sql';
$filePath = $backupDir . '/' . $filename;
@file_put_contents($filePath, $sql);

db_run('INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?,?,?,?)',
    [(int) current_user()['id'], 'backup', 'Membuat backup ' . $filename, client_ip()]);

header('Content-Type: application/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($sql));
header('Cache-Control: no-store, no-cache, must-revalidate');
echo $sql;
exit;
