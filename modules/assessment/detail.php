<?php
/** modules/assessment/detail.php - Form penilaian & riwayat per siswa. */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$studentId = can_manage() ? (int) query('student_id', 0) : (int) current_user()['student_id'];
$student = db_one('SELECT * FROM students WHERE id = ?', [$studentId]);
if (!$student) redirect('modules/assessment/index.php');
$page_title = 'Penilaian — ' . $student['name'];

$latest = db_one('SELECT * FROM competency_assessments WHERE student_id = ? ORDER BY id DESC LIMIT 1', [$studentId]);
$history = db_all('SELECT c.*, u.full_name AS assessor_name FROM competency_assessments c
    LEFT JOIN users u ON u.id = c.assessor_id WHERE c.student_id = ? ORDER BY c.id DESC', [$studentId]);

$remediation = db_all('SELECT * FROM remediation WHERE student_id = ? ORDER BY id DESC', [$studentId]);
$enrichment = db_all('SELECT * FROM enrichment WHERE student_id = ? ORDER BY id DESC', [$studentId]);

$score = $latest ? (float)$latest['total_score'] : null;
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <p style="margin:0 0 6px"><a href="<?= url('modules/assessment/index.php') ?>">← Daftar Penilaian</a></p>
        <h1><?= e($student['name']) ?></h1>
        <p><?= e($student['class_name']) ?> · NIS <?= e($student['nis']) ?><?= $score !== null ? ' · Nilai terakhir: ' . fmt_score($score) : '' ?></p>
    </div>
    <?php if ($latest): ?><a class="btn btn-outline" href="<?= url('modules/assessment/print.php?student_id=' . $studentId) ?>" target="_blank">🖨️ Cetak</a><?php endif; ?>
</div>

<?php if (can_manage()): ?>
<div class="card">
    <div class="card-head"><h2>Form Penilaian Baru</h2><span class="badge badge-blue">Skala 1–4</span></div>
    <form method="post" action="<?= url('modules/assessment/process.php') ?>">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="student_id" value="<?= $studentId ?>">
        <div class="form-grid">
            <?php foreach (COMPETENCY_LABELS as $key => $label): ?>
            <div class="form-group">
                <label><?= e($label) ?> <span class="badge badge-blue"><?= COMPETENCY_WEIGHTS[$key] ?>%</span></label>
                <select name="<?= $key ?>" required>
                    <?php foreach (SCALE_OPTIONS as $val => $text): ?>
                        <option value="<?= $val ?>" <?= ($latest && (int)$latest[$key] === $val) ? 'selected' : '' ?>><?= e($text) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endforeach; ?>
            <div class="form-group">
                <label>Tanggal Penilaian</label>
                <input type="date" name="assessment_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Catatan Asesor</label>
                <textarea name="notes" rows="2" style="min-height:48px"><?= e($latest['notes'] ?? '') ?></textarea>
            </div>
        </div>
        <button class="btn btn-accent btn-lg" type="submit">💾 Simpan Penilaian</button>
    </form>
</div>
<?php endif; ?>

<?php if ($latest): ?>
<div class="card">
    <div class="card-head"><h2>Rincian Nilai Terakhir</h2>
        <span class="badge <?= score_badge_class($score) ?>"><?= fmt_score($score) ?> — <?= e(score_predicate($score)) ?></span>
    </div>
    <?php foreach (COMPETENCY_LABELS as $key => $label): ?>
        <?php $val = (int)$latest[$key]; $pct = ($val / 4) * 100; ?>
        <div class="chart-row">
            <strong><?= e($label) ?></strong>
            <div class="chart-bar <?= $pct < 50 ? 'low' : '' ?>"><span style="width:<?= $pct ?>%"></span></div>
            <div class="chart-value"><?= $val ?>/4</div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="grid grid-2">
    <div class="card">
        <div class="card-head"><h3>Riwayat Penilaian</h3></div>
        <?php if (!$history): ?><p class="muted">Belum ada penilaian.</p><?php endif; ?>
        <?php foreach ($history as $h): ?>
            <div style="padding:10px 0;border-bottom:1px solid var(--line)">
                <strong><?= e(format_date($h['assessment_date'])) ?></strong>
                <span class="badge <?= score_badge_class((float)$h['total_score']) ?>"><?= fmt_score($h['total_score']) ?></span>
                <br><small class="muted">Oleh <?= e($h['assessor_name'] ?? 'Sistem') ?></small>
                <?php if ($h['notes']): ?><p style="font-size:.85rem;margin:4px 0 0"><?= e($h['notes']) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-head"><h3>Tindak Lanjut</h3>
            <?php if (can_manage()): ?><span class="badge badge-blue">Otomatis</span><?php endif; ?>
        </div>
        <?php if ($score !== null): ?>
            <?php if ($score < 75): ?>
                <div class="alert alert-danger"><strong>Perlu Remediasi</strong><br>Nilai di bawah 75. Siswa memerlukan program remediasi pada aspek dengan skor terendah.</div>
            <?php elseif ($score >= 85): ?>
                <div class="alert alert-success"><strong>Siap Pengayaan</strong><br>Nilai sangat baik. Siswa dapat mengikuti program pengayaan / tantangan lanjutan.</div>
            <?php else: ?>
                <div class="alert alert-info"><strong>Kompeten</strong><br>Nilai sudah memenuhi standar. Pertahankan konsistensi.</div>
            <?php endif; ?>
        <?php else: ?>
            <p class="muted">Belum ada penilaian untuk menentukan tindak lanjut.</p>
        <?php endif; ?>
        <h4>Program Remediasi</h4>
        <?php if (!$remediation): ?><p class="muted">Belum ada program remediasi.</p><?php endif; ?>
        <?php foreach ($remediation as $r): ?>
            <div style="padding:8px 0;border-bottom:1px solid var(--line);font-size:.85rem">
                <strong><?= e($r['competency']) ?></strong>
                <span class="badge badge-<?= ['rencana'=>'gray','proses'=>'yellow','selesai'=>'green'][$r['status']] ?>"><?= e(ucfirst($r['status'])) ?></span>
                <p><?= e(str_limit($r['material'], 100)) ?></p>
            </div>
        <?php endforeach; ?>
        <h4>Program Pengayaan</h4>
        <?php if (!$enrichment): ?><p class="muted">Belum ada program pengayaan.</p><?php endif; ?>
        <?php foreach ($enrichment as $r): ?>
            <div style="padding:8px 0;border-bottom:1px solid var(--line);font-size:.85rem">
                <strong><?= e($r['competency']) ?></strong>
                <span class="badge badge-<?= ['rencana'=>'gray','proses'=>'yellow','selesai'=>'green'][$r['status']] ?>"><?= e(ucfirst($r['status'])) ?></span>
                <p><?= e(str_limit($r['material'], 100)) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
