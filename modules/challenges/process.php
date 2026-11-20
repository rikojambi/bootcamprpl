<?php
/** modules/challenges/process.php - Mulai/selesai attempt live coding. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
if (!is_post()) redirect('modules/challenges/index.php');
csrf_guard();

$studentId = (int) (current_user()['student_id'] ?? 0);
$action = post('action');

// Guru dapat menilai attempt tanpa memiliki student_id
if ($action === 'grade' && can_manage()) {
    $attemptId = (int) post('attempt_id', 0);
    $score = max(0, min(100, (int) post('score', 0)));
    db_run("UPDATE coding_attempts SET examiner_score = ?, examiner_notes = ?, status = 'dinilai' WHERE id = ?",
        [$score, trim(post('notes')), $attemptId]);
    db_run('INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?,?,?,?)',
        [(int) current_user()['id'], 'coding_grade', 'Menilai attempt #' . $attemptId . ' = ' . $score, client_ip()]);
    flash('success', 'Penilaian live coding tersimpan. Nilai: ' . $score);
    redirect('modules/challenges/result.php?id=' . $attemptId);
}

if (!$studentId) {
    flash('warning', 'Fitur ini hanya untuk akun siswa.');
    redirect('modules/challenges/index.php');
}

if ($action === 'start') {
    $challengeId = (int) post('challenge_id', 0);
    if (!db_one('SELECT id FROM coding_challenges WHERE id = ?', [$challengeId])) {
        flash('danger', 'Tantangan tidak ditemukan.');
        redirect('modules/challenges/index.php');
    }
    // Tutup attempt lama yang masih berjalan
    db_run("UPDATE coding_attempts SET status = 'selesai', ended_at = NOW()
            WHERE student_id = ? AND challenge_id = ? AND status = 'berjalan'",
        [$studentId, $challengeId]);
    db_run('INSERT INTO coding_attempts (student_id, challenge_id, started_at, status)
            VALUES (?,?,NOW(),"berjalan")', [$studentId, $challengeId]);
    flash('success', 'Tantangan dimulai. Timer berjalan!');
    redirect('modules/challenges/detail.php?id=' . $challengeId);
}

if ($action === 'submit') {
    $attemptId = (int) post('attempt_id', 0);
    $attempt = db_one('SELECT * FROM coding_attempts WHERE id = ? AND student_id = ?', [$attemptId, $studentId]);
    if (!$attempt) {
        flash('danger', 'Attempt tidak ditemukan.');
        redirect('modules/challenges/index.php');
    }
    $duration = (int) db_value('SELECT TIMESTAMPDIFF(SECOND, started_at, NOW()) FROM coding_attempts WHERE id = ?', [$attemptId]);
    db_run("UPDATE coding_attempts SET status = 'selesai', ended_at = NOW(), duration_sec = ?, result_text = ? WHERE id = ?",
        [$duration, trim(post('result_text')), $attemptId]);
    flash('success', 'Hasil terkumpul. Durasi: ' . floor($duration / 60) . ' menit ' . ($duration % 60) . ' detik. Menunggu penilaian guru.');
    redirect('modules/challenges/history.php');
}

flash('danger', 'Aksi tidak dikenal.');
redirect('modules/challenges/index.php');
