<?php
/** modules/reports/process.php - Simpan program remediasi/pengayaan. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();
if (!is_post()) redirect('modules/reports/index.php');
csrf_guard();

if (post('action') !== 'assign') {
    flash('danger', 'Aksi tidak dikenal.');
    redirect('modules/reports/index.php');
}

$studentId = (int) post('student_id', 0);
if (!db_one('SELECT id FROM students WHERE id = ?', [$studentId])) {
    flash('danger', 'Siswa tidak ditemukan.');
    redirect('modules/reports/index.php');
}

$program = post('program') === 'enrichment' ? 'enrichment' : 'remediation';
$table = $program === 'enrichment' ? 'enrichment' : 'remediation';
$status = in_array(post('status'), ['rencana','proses','selesai'], true) ? post('status') : 'rencana';
$due = post('due_date');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$due)) $due = null;

db_run("INSERT INTO $table (student_id, competency, material, task, notes, status, due_date)
        VALUES (?,?,?,?,?,?,?)", [
    $studentId,
    trim(post('competency')),
    trim(post('material')),
    trim(post('task')),
    trim(post('notes')) ?: null,
    $status,
    $due,
]);

db_run('INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?,?,?,?)',
    [(int) current_user()['id'], 'assign_' . $program,
     'Menetapkan ' . $program . ' untuk siswa #' . $studentId, client_ip()]);

flash('success', 'Program ' . ($program === 'enrichment' ? 'pengayaan' : 'remediasi') . ' berhasil ditetapkan.');
redirect('modules/students/detail.php?id=' . $studentId);
