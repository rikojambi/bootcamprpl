<?php
/** modules/seminar/detail.php - Detail seminar, bedah kode, dan penilaian. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$studentId = (int) query('id', 0);
if (!can_manage()) {
    $studentId = (int) (current_user()['student_id'] ?? 0);
}
if ($studentId < 1) redirect('modules/seminar/index.php');

$student = db_one('SELECT * FROM students WHERE id = ?', [$studentId]);
if (!$student) redirect('modules/seminar/index.php');

$profile = db_one('SELECT * FROM seminar_profiles WHERE student_id = ?', [$studentId]);
if (!$profile) {
    $profile = [
        'id' => 0, 'student_id' => $studentId, 'pkl_place' => $student['pkl_place'] ?? '',
        'pkl_period' => '', 'app_title' => $student['pkl_title'] ?? '',
        'technologies' => $student['technologies'] ?? '', 'problem_desc' => $student['problem_desc'] ?? '',
        'requirement' => '', 'architecture' => '', 'erd' => '', 'folder_structure' => '',
        'demo_notes' => '', 'features' => $student['features'] ?? '', 'status' => 'draft',
    ];
}

$latest = db_one('SELECT * FROM seminar_assessments WHERE student_id = ? ORDER BY id DESC LIMIT 1', [$studentId]);
$questions = db_all('SELECT * FROM question_bank ORDER BY FIELD(category, "HTML","CSS","JavaScript","PHP","MySQL","API","Authentication","Debugging","Security"), id');
$questionsByCategory = [];
foreach ($questions as $q) $questionsByCategory[$q['category']][] = $q;

$challenges = db_all('SELECT id, code, title, level, description, duration_min, competency FROM coding_challenges ORDER BY FIELD(level, "beginner","intermediate","advanced"), id');

$selectedQuestions = $latest && !empty($latest['selected_question_ids'])
    ? array_map('intval', explode(',', $latest['selected_question_ids'])) : [];

$page_title = 'Seminar — ' . $student['name'];
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1><?= e($student['name']) ?></h1>
        <p><?= e($student['class_name']) ?> · NIS <?= e($student['nis']) ?> · <?= e($profile['pkl_place']) ?></p>
    </div>
    <div class="btn-group">
        <a href="<?= url('modules/seminar/index.php') ?>" class="btn btn-light">← Kembali</a>
        <?php if (can_manage()): ?>
        <a href="<?= url('modules/seminar/print.php?id=' . $studentId) ?>" target="_blank" class="btn btn-outline">🖨️ Lembar Observasi</a>
        <?php endif; ?>
    </div>
</div>

<div class="tabs">
    <button class="tab active" data-tab="identitas">1. Identitas</button>
    <button class="tab" data-tab="presentasi">2. Presentasi PKL</button>
    <button class="tab" data-tab="bedah">3. Bedah Kode</button>
    <button class="tab" data-tab="challenge">4. Developer Challenge</button>
    <button class="tab" data-tab="nilai">5. Penilaian</button>
</div>

<div class="tab-container">
<!-- ============ TAB 1: IDENTITAS ============ -->
<section class="tab-panel active" data-panel="identitas">
    <div class="card">
        <div class="card-head"><h2>Identitas Siswa & Aplikasi</h2>
            <span class="badge <?= $profile['status'] === 'siap' ? 'badge-green' : 'badge-gray' ?>"><?= e(ucfirst($profile['status'])) ?></span>
        </div>
        <?php if (can_manage()): ?>
        <form method="post" action="<?= url('modules/seminar/process.php') ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="save_profile">
            <input type="hidden" name="student_id" value="<?= $studentId ?>">
            <div class="form-grid">
                <div class="form-group"><label>Tempat PKL</label><input type="text" name="pkl_place" value="<?= e($profile['pkl_place']) ?>" required></div>
                <div class="form-group"><label>Periode PKL</label><input type="text" name="pkl_period" value="<?= e($profile['pkl_period']) ?>" placeholder="Contoh: Januari - Maret 2026"></div>
                <div class="form-group full"><label>Judul Aplikasi</label><input type="text" name="app_title" value="<?= e($profile['app_title']) ?>" required></div>
                <div class="form-group full"><label>Teknologi yang Digunakan</label><textarea name="technologies" rows="2"><?= e($profile['technologies']) ?></textarea></div>
                <div class="form-group full"><label>Deskripsi Masalah</label><textarea name="problem_desc" rows="3"><?= e($profile['problem_desc']) ?></textarea></div>
                <div class="form-group full"><label>Fitur Aplikasi</label><textarea name="features" rows="3"><?= e($profile['features']) ?></textarea></div>
                <div class="form-group">
                    <label>Status Seminar</label>
                    <select name="status">
                        <?php foreach (['draft'=>'Draft','siap'=>'Siap','dinilai'=>'Dinilai'] as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= $profile['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">💾 Simpan Identitas</button>
        </form>
        <?php else: ?>
            <div class="grid grid-2">
                <div><strong>Tempat PKL</strong><p><?= e($profile['pkl_place']) ?></p></div>
                <div><strong>Periode</strong><p><?= e($profile['pkl_period'] ?: '-') ?></p></div>
                <div class="form-group full"><strong>Judul Aplikasi</strong><p><?= e($profile['app_title']) ?></p></div>
                <div class="form-group full"><strong>Teknologi</strong><p><?= nl2br(e($profile['technologies'])) ?></p></div>
                <div class="form-group full"><strong>Deskripsi Masalah</strong><p><?= nl2br(e($profile['problem_desc'])) ?></p></div>
                <div class="form-group full"><strong>Fitur Aplikasi</strong><p><?= nl2br(e($profile['features'])) ?></p></div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============ TAB 2: PRESENTASI ============ -->
<section class="tab-panel" data-panel="presentasi">
    <div class="card">
        <div class="card-head"><h2>Presentasi Hasil PKL</h2></div>
        <?php if (can_manage()): ?>
        <form method="post" action="<?= url('modules/seminar/process.php') ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="save_presentation">
            <input type="hidden" name="student_id" value="<?= $studentId ?>">
            <?php
            $fields = [
                'requirement'     => ['Analisis Kebutuhan', 'Tuliskan kebutuhan fungsional dan nonfungsional aplikasi.'],
                'architecture'    => ['Arsitektur Aplikasi', 'Jelaskan alur frontend, backend, dan database beserta teknologinya.'],
                'erd'             => ['ERD / Struktur Database', 'Sebutkan tabel, primary key, dan foreign key utama.'],
                'folder_structure'=> ['Struktur Folder', 'Tuliskan struktur folder proyek beserta fungsinya.'],
                'demo_notes'      => ['Catatan Demo', 'Langkah-langkah demonstrasi aplikasi yang akan ditampilkan.'],
            ];
            foreach ($fields as $key => [$label, $hint]):
            ?>
                <div class="form-group">
                    <label><?= e($label) ?></label>
                    <textarea name="<?= $key ?>" rows="3" placeholder="<?= e($hint) ?>"><?= e($profile[$key]) ?></textarea>
                </div>
            <?php endforeach; ?>
            <button class="btn btn-primary" type="submit">💾 Simpan Presentasi</button>
        </form>
        <?php else: ?>
            <?php foreach (['requirement'=>'Analisis Kebutuhan','architecture'=>'Arsitektur Aplikasi','erd'=>'ERD','folder_structure'=>'Struktur Folder','demo_notes'=>'Catatan Demo'] as $key=>$label): ?>
                <h3><?= e($label) ?></h3>
                <p><?= nl2br(e($profile[$key] ?: '-')) ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ============ TAB 3: BEDAH KODE ============ -->
<section class="tab-panel" data-panel="bedah">
    <div class="card">
        <div class="card-head"><h2>Bedah Kode — Pilih Pertanyaan dari Bank</h2>
            <span class="badge badge-blue"><?= count($questions) ?> pertanyaan</span></div>
        <p class="muted">Guru dapat memilih beberapa pertanyaan untuk diajukan saat seminar. Pertanyaan yang dipilih tersimpan bersama penilaian.</p>
        <?php if (can_manage()): ?>
        <form method="post" action="<?= url('modules/seminar/process.php') ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="save_questions">
            <input type="hidden" name="student_id" value="<?= $studentId ?>">
            <?php foreach ($questionsByCategory as $category => $items): ?>
                <h3 style="margin-top:18px"><?= e($category) ?></h3>
                <?php foreach ($items as $q): ?>
                    <label class="form-check" style="align-items:flex-start">
                        <input type="checkbox" name="question_ids[]" value="<?= (int) $q['id'] ?>"
                               <?= in_array((int) $q['id'], $selectedQuestions, true) ? 'checked' : '' ?>>
                        <span><?= e($q['question']) ?>
                            <small class="muted">(<?= e($q['difficulty']) ?>)</small>
                            <?php if (!empty($q['expected_answer'])): ?>
                            <br><small class="muted">Jawaban: <?= e(str_limit($q['expected_answer'], 140)) ?></small>
                            <?php endif; ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <div class="form-actions" style="margin-top:20px">
                <button class="btn btn-primary" type="submit">💾 Simpan Pilihan Pertanyaan</button>
            </div>
        </form>
        <?php else: ?>
            <?php foreach ($questionsByCategory as $category => $items): ?>
                <h3><?= e($category) ?></h3>
                <ol><?php foreach ($items as $q): ?><li><?= e($q['question']) ?></li><?php endforeach; ?></ol>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ============ TAB 4: DEVELOPER CHALLENGE ============ -->
<section class="tab-panel" data-panel="challenge">
    <div class="card">
        <div class="card-head"><h2>Developer Challenge</h2></div>
        <p>Guru memilih tantangan yang sesuai dengan aplikasi siswa. Tantangan dapat dikerjakan langsung pada sesi seminar.</p>
        <div class="grid grid-3">
            <?php foreach ($challenges as $c): ?>
                <div class="card tight" style="margin:0">
                    <span class="badge badge-<?= ['beginner'=>'green','intermediate'=>'blue','advanced'=>'purple'][$c['level']] ?>">
                        <?= e(CHALLENGE_LEVELS[$c['level']]) ?></span>
                    <h4 style="margin-top:10px"><?= e($c['title']) ?></h4>
                    <p class="muted" style="font-size:.85rem"><?= e(str_limit($c['description'], 100)) ?></p>
                    <small class="muted">⏱ <?= (int) $c['duration_min'] ?> menit · <?= e($c['competency']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ TAB 5: PENILAIAN ============ -->
<section class="tab-panel" data-panel="nilai">
    <div class="card">
        <div class="card-head"><h2>Penilaian Seminar</h2>
            <?php if ($latest): ?><span class="badge <?= score_badge_class((float)$latest['total_score']) ?>">Terakhir: <?= fmt_score($latest['total_score']) ?></span><?php endif; ?>
        </div>
        <p class="muted">Skala 0–100 per aspek. Bobot: <?php foreach (SEMINAR_LABELS as $k=>$label) echo e($label) . ' ' . SEMINAR_WEIGHTS[$k] . '%, '; ?></p>
        <?php if (can_manage()): ?>
        <form method="post" action="<?= url('modules/seminar/process.php') ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="save_assessment">
            <input type="hidden" name="student_id" value="<?= $studentId ?>">
            <div class="form-grid">
                <?php foreach (SEMINAR_LABELS as $key => $label): ?>
                    <div class="form-group">
                        <label><?= e($label) ?> (<?= SEMINAR_WEIGHTS[$key] ?>%)</label>
                        <input type="number" min="0" max="100" name="<?= $key ?>"
                               value="<?= (int) ($latest[$key] ?? 0) ?>" required>
                    </div>
                <?php endforeach; ?>
                <div class="form-group">
                    <label>Tantangan Terpilih</label>
                    <select name="challenge_id">
                        <option value="">- Tidak ada -</option>
                        <?php foreach ($challenges as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= ($latest && (int)$latest['challenge_id'] === (int)$c['id']) ? 'selected' : '' ?>>
                                <?= e($c['code'] . ' - ' . $c['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Penilaian</label>
                    <input type="date" name="assessed_at" value="<?= e($latest ? substr($latest['assessed_at'],0,10) : date('Y-m-d')) ?>">
                </div>
                <div class="form-group full"><label>Lembar Observasi</label>
                    <textarea name="observation_sheet" rows="3"><?= e($latest['observation_sheet'] ?? '') ?></textarea></div>
                <div class="form-group full"><label>Catatan Asesor</label>
                    <textarea name="assessor_notes" rows="3"><?= e($latest['assessor_notes'] ?? '') ?></textarea></div>
            </div>
            <button class="btn btn-accent" type="submit">✅ Simpan Penilaian</button>
        </form>
        <?php elseif ($latest): ?>
            <div class="grid grid-2">
                <?php foreach (SEMINAR_LABELS as $key=>$label): ?>
                    <div class="chart-row"><strong><?= e($label) ?></strong>
                        <div class="chart-bar"><span style="width:<?= (int)$latest[$key] ?>%"></span></div>
                        <div class="chart-value"><?= (int)$latest[$key] ?></div></div>
                <?php endforeach; ?>
            </div>
            <h3>Catatan Asesor</h3><p><?= nl2br(e($latest['assessor_notes'] ?: '-')) ?></p>
        <?php else: ?>
            <p class="muted">Belum ada penilaian untuk siswa ini.</p>
        <?php endif; ?>
    </div>

    <?php if ($latest): ?>
    <div class="card">
        <div class="card-head"><h3>Riwayat Penilaian Seminar</h3></div>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Tanggal</th><th>Asesor</th><th>Nilai</th><th>Catatan</th></tr></thead>
            <tbody>
            <?php foreach (db_all('SELECT sa.*, u.full_name AS assessor_name FROM seminar_assessments sa LEFT JOIN users u ON u.id = sa.assessor_id WHERE sa.student_id = ? ORDER BY sa.id DESC', [$studentId]) as $a): ?>
                <tr><td><?= e(format_date($a['assessed_at'])) ?></td><td><?= e($a['assessor_name'] ?? '-') ?></td>
                    <td><span class="badge <?= score_badge_class((float)$a['total_score']) ?>"><?= fmt_score($a['total_score']) ?></span></td>
                    <td><?= e(str_limit($a['assessor_notes'], 80)) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div>
    <?php endif; ?>
</section>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
