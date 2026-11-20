<?php
/** modules/seminar/process.php - Penyimpanan data seminar (CSRF + role). */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();
if (!is_post()) redirect('modules/seminar/index.php');
csrf_guard();

$action = post('action');
$studentId = (int) post('student_id', 0);
$student = db_one('SELECT id FROM students WHERE id = ?', [$studentId]);
if (!$student) {
    flash('danger', 'Siswa tidak ditemukan.');
    redirect('modules/seminar/index.php');
}

switch ($action) {
    case 'save_profile':
        db_run('INSERT INTO seminar_profiles
            (student_id, pkl_place, pkl_period, app_title, technologies, problem_desc, features, status)
            VALUES (?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
            pkl_place = VALUES(pkl_place), pkl_period = VALUES(pkl_period),
            app_title = VALUES(app_title), technologies = VALUES(technologies),
            problem_desc = VALUES(problem_desc), features = VALUES(features), status = VALUES(status)', [
            $studentId,
            trim(post('pkl_place')), trim(post('pkl_period')), trim(post('app_title')),
            trim(post('technologies')), trim(post('problem_desc')), trim(post('features')),
            in_array(post('status'), ['draft','siap','dinilai'], true) ? post('status') : 'draft',
        ]);
        flash('success', 'Identitas seminar berhasil disimpan.');
        break;

    case 'save_presentation':
        db_run('INSERT INTO seminar_profiles
            (student_id, requirement, architecture, erd, folder_structure, demo_notes)
            VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
            requirement = VALUES(requirement), architecture = VALUES(architecture),
            erd = VALUES(erd), folder_structure = VALUES(folder_structure),
            demo_notes = VALUES(demo_notes)', [
            $studentId, trim(post('requirement')), trim(post('architecture')),
            trim(post('erd')), trim(post('folder_structure')), trim(post('demo_notes')),
        ]);
        flash('success', 'Presentasi PKL berhasil disimpan.');
        break;

    case 'save_questions':
        $ids = $_POST['question_ids'] ?? [];
        if (!is_array($ids)) $ids = [];
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $latest = db_one('SELECT id FROM seminar_assessments WHERE student_id = ? ORDER BY id DESC LIMIT 1', [$studentId]);
        if ($latest) {
            db_run('UPDATE seminar_assessments SET selected_question_ids = ? WHERE id = ?',
                [implode(',', $ids), (int) $latest['id']]);
        } else {
            // Simpan pilihan sementara pada session bila penilaian belum dibuat
            $_SESSION['seminar_questions_' . $studentId] = implode(',', $ids);
        }
        flash('success', count($ids) . ' pertanyaan bedah kode dipilih.');
        break;

    case 'save_assessment':
        $scores = compute_seminar_score($_POST);
        $date = post('assessed_at') ?: date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');
        $questionIds = $_POST['question_ids'] ?? [];
        if (!is_array($questionIds)) $questionIds = [];
        $questionIds = array_values(array_unique(array_map('intval', $questionIds)));
        if (empty($questionIds) && !empty($_SESSION['seminar_questions_' . $studentId])) {
            $questionIds = array_map('intval', explode(',', $_SESSION['seminar_questions_' . $studentId]));
        }

        $columns = ['problem_understanding','architecture_db','code_understanding',
            'live_coding','testing','communication'];
        $params = [$studentId, (int) current_user()['id']];
        foreach ($columns as $col) $params[] = $scores[$col];
        $params[] = $scores['total_score'];
        $params[] = implode(',', $questionIds);
        $params[] = (int) post('challenge_id', 0) ?: null;
        $params[] = trim(post('observation_sheet'));
        $params[] = trim(post('assessor_notes'));
        $params[] = $date . ' ' . date('H:i:s');

        db_run('INSERT INTO seminar_assessments
            (student_id, assessor_id, problem_understanding, architecture_db, code_understanding,
             live_coding, testing, communication, total_score, selected_question_ids,
             challenge_id, observation_sheet, assessor_notes, assessed_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', $params);

        db_run("UPDATE seminar_profiles SET status = 'dinilai' WHERE student_id = ?", [$studentId]);
        db_run('INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?,?,?,?)',
            [(int) current_user()['id'], 'seminar_assessment',
             'Menilai seminar ' . $student['id'] . ' - ' . fmt_score($scores['total_score']), client_ip()]);

        unset($_SESSION['seminar_questions_' . $studentId]);
        flash('success', 'Penilaian seminar tersimpan. Nilai total: ' . fmt_score($scores['total_score']));
        break;

    default:
        flash('danger', 'Aksi tidak dikenal.');
}

redirect('modules/seminar/detail.php?id=' . $studentId);
