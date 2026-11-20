<?php
/** modules/reports/export.php - Ekspor data siswa & nilai ke CSV (lokal). */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();

$rows = db_all('SELECT s.nis, s.name, s.class_name, s.email, s.phone,
        c.total_score, c.assessment_date,
        (SELECT COUNT(*) FROM student_jobsheets sj WHERE sj.student_id = s.id AND sj.status = "selesai") AS jobsheets_done,
        (SELECT COUNT(*) FROM student_progress sp WHERE sp.student_id = s.id AND sp.status = "selesai") AS materials_done
    FROM students s
    LEFT JOIN competency_assessments c ON c.id = (SELECT MAX(id) FROM competency_assessments WHERE student_id = s.id)
    ORDER BY s.class_name, s.name');

$filename = 'data_siswa_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');

$out = fopen('php://output', 'w');
// BOM agar Excel membaca UTF-8 dengan benar
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['NIS', 'Nama', 'Kelas', 'Email', 'Telepon', 'Nilai Kompetensi', 'Tanggal Penilaian', 'Jobsheet Selesai', 'Materi Selesai']);
foreach ($rows as $r) {
    fputcsv($out, [
        $r['nis'], $r['name'], $r['class_name'], $r['email'], $r['phone'],
        $r['total_score'] !== null ? fmt_score($r['total_score']) : '',
        $r['assessment_date'] ?: '',
        (int)$r['jobsheets_done'],
        (int)$r['materials_done'],
    ]);
}
fclose($out);
exit;
