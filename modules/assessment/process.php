<?php
/** modules/assessment/process.php - Simpan penilaian kompetensi. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();
if (!is_post()) redirect('modules/assessment/index.php');
csrf_guard();

$studentId = (int) post('student_id', 0);
if (!db_one('SELECT id FROM students WHERE id = ?', [$studentId])) {
    flash('danger', 'Siswa tidak ditemukan.');
    redirect('modules/assessment/index.php');
}

$scores = compute_weighted_score($_POST, COMPETENCY_WEIGHTS);
$date = post('assessment_date') ?: date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');

$params = [$studentId, (int) current_user()['id'], $date];
foreach (COMPETENCY_WEIGHTS as $field => $weight) $params[] = $scores[$field];
$params[] = $scores['total_score'];
$params[] = trim(post('notes')) ?: null;

$columns = implode(',', array_merge(
    ['student_id','assessor_id','assessment_date'],
    array_keys(COMPETENCY_WEIGHTS),
    ['total_score','notes']
));
$placeholders = implode(',', array_fill(0, count($params), '?'));

db_run("INSERT INTO competency_assessments ($columns) VALUES ($placeholders)", $params);
db_run('INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?,?,?,?)',
    [(int) current_user()['id'], 'competency_assessment',
     'Menilai kompetensi siswa #' . $studentId . ' = ' . fmt_score($scores['total_score']), client_ip()]);

flash('success', 'Penilaian tersimpan. Nilai total: ' . fmt_score($scores['total_score']) . ' (' . score_predicate($scores['total_score']) . ')');
redirect('modules/assessment/detail.php?student_id=' . $studentId);
