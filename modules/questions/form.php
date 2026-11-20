<?php
/** modules/questions/form.php - Tambah/edit pertanyaan bank soal. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_manager();
$id = (int) query('id', 0);
$q = $id ? db_one('SELECT * FROM question_bank WHERE id = ?', [$id]) : null;
$page_title = $q ? 'Edit Pertanyaan' : 'Tambah Pertanyaan';
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <p style="margin:0 0 6px"><a href="<?= url('modules/questions/index.php') ?>">← Bank Soal</a></p>
        <h1><?= e($page_title) ?></h1>
    </div>
</div>

<div class="card" style="max-width:900px">
    <form method="post" action="<?= url('modules/questions/process.php') ?>">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="form-grid">
            <div class="form-group">
                <label for="category">Kategori</label>
                <select name="category" id="category" required>
                    <?php foreach (QUESTION_CATEGORIES as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($q['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="difficulty">Tingkat Kesulitan</label>
                <select name="difficulty" id="difficulty" required>
                    <?php foreach (['mudah'=>'Mudah','sedang'=>'Sedang','sulit'=>'Sulit'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($q['difficulty'] ?? 'sedang') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group full">
                <label for="question">Pertanyaan</label>
                <textarea name="question" id="question" rows="3" required><?= e($q['question'] ?? '') ?></textarea>
            </div>
            <div class="form-group full">
                <label for="expected_answer">Jawaban / Poin Kunci</label>
                <textarea name="expected_answer" id="expected_answer" rows="4"><?= e($q['expected_answer'] ?? '') ?></textarea>
                <span class="hint">Tuliskan jawaban singkat atau poin-poin yang diharapkan dari siswa.</span>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">💾 Simpan</button>
            <a class="btn btn-light" href="<?= url('modules/questions/index.php') ?>">Batal</a>
        </div>
    </form>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
