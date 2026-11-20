<?php
/** modules/questions/process.php - Simpan/hapus pertanyaan bank soal. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();

$action = post('action', query('action', ''));

if ($action === 'delete') {
    // Hapus via GET (dengan konfirmasi JS di sisi klien)
    $id = (int) query('id', 0);
    if ($id) {
        db_run('DELETE FROM question_bank WHERE id = ?', [$id]);
        flash('success', 'Pertanyaan berhasil dihapus.');
    }
    redirect('modules/questions/index.php');
}

if (!is_post()) redirect('modules/questions/index.php');
csrf_guard();

if ($action === 'save') {
    $id = (int) post('id', 0);
    $category = post('category', 'PHP');
    if (!in_array($category, QUESTION_CATEGORIES, true)) $category = 'PHP';
    $difficulty = post('difficulty', 'sedang');
    if (!in_array($difficulty, ['mudah','sedang','sulit'], true)) $difficulty = 'sedang';
    $question = trim(post('question'));
    $answer = trim(post('expected_answer'));

    if ($question === '') {
        flash('danger', 'Pertanyaan wajib diisi.');
        redirect('modules/questions/form.php' . ($id ? '?id=' . $id : ''));
    }

    if ($id) {
        db_run('UPDATE question_bank SET category = ?, question = ?, expected_answer = ?, difficulty = ? WHERE id = ?',
            [$category, $question, $answer ?: null, $difficulty, $id]);
        flash('success', 'Pertanyaan berhasil diperbarui.');
    } else {
        db_run('INSERT INTO question_bank (category, question, expected_answer, difficulty) VALUES (?,?,?,?)',
            [$category, $question, $answer ?: null, $difficulty]);
        flash('success', 'Pertanyaan berhasil ditambahkan.');
    }
    redirect('modules/questions/index.php');
}

flash('danger', 'Aksi tidak dikenal.');
redirect('modules/questions/index.php');
