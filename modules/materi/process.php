<?php
/** modules/materi/process.php - Simpan progres belajar siswa. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
if (!is_post()) redirect('modules/materi/index.php');
csrf_guard();

$studentId = (int) (current_user()['student_id'] ?? 0);
$sessionId = (int) post('session_id', 0);
$status = post('status', 'belum');
if (!in_array($status, ['belum','sedang','selesai'], true)) $status = 'belum';

if (!$studentId) {
    flash('warning', 'Progres hanya dapat disimpan oleh akun siswa.');
} else {
    db_run('INSERT INTO student_progress (student_id, session_id, status, notes, completed_at)
            VALUES (?,?,?,?,?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes),
            completed_at = IF(VALUES(status) = "selesai", NOW(), NULL)', [
        $studentId, $sessionId, $status, trim(post('notes')),
        $status === 'selesai' ? date('Y-m-d H:i:s') : null,
    ]);
    flash('success', 'Progres materi berhasil disimpan.');
}

$no = (int) db_value('SELECT session_no FROM bootcamp_sessions WHERE id = ?', [$sessionId]);
redirect('modules/materi/detail.php?no=' . max(1, $no));
