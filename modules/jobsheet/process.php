<?php
/** modules/jobsheet/process.php - Simpan status penyelesaian jobsheet. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
if (!is_post()) redirect('modules/jobsheet/index.php');
csrf_guard();

$jobsheetId = (int) post('jobsheet_id', 0);
$studentId  = (int) post('student_id', 0);
$status     = post('status', 'belum');
if (!in_array($status, ['belum','proses','selesai'], true)) $status = 'belum';

// Siswa hanya boleh mengubah data dirinya sendiri
if (!can_manage() && $studentId !== (int) current_user()['student_id']) {
    http_response_code(403);
    exit('Akses ditolak.');
}
if (!db_one('SELECT id FROM students WHERE id = ?', [$studentId]) ||
    !db_one('SELECT id FROM jobsheets WHERE id = ?', [$jobsheetId])) {
    flash('danger', 'Data jobsheet atau siswa tidak valid.');
    redirect('modules/jobsheet/index.php');
}

db_run('INSERT INTO student_jobsheets (student_id, jobsheet_id, status, notes, completed_at)
        VALUES (?,?,?,?,?)
        ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes),
        completed_at = IF(VALUES(status) = "selesai", NOW(), NULL)', [
    $studentId, $jobsheetId, $status, trim(post('notes')),
    $status === 'selesai' ? date('Y-m-d H:i:s') : null,
]);

flash('success', 'Progres jobsheet berhasil disimpan.');
redirect('modules/jobsheet/detail.php?id=' . $jobsheetId . '&student_id=' . $studentId);
