<?php
/** modules/reports/index.php - Laporan & tindak lanjut (remediasi/pengayaan). */
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/auth/auth.php';

require_login();
$page_title = 'Laporan & Tindak Lanjut';

// Siswa hanya melihat rekomendasi dirinya sendiri; guru/admin melihat semua siswa.
$ownStudentId = can_manage() ? 0 : (int) current_user()['student_id'];
$rows = db_all('SELECT s.*, c.total_score, c.assessment_date,
        (SELECT COUNT(*) FROM remediation r WHERE r.student_id = s.id) AS rem_count,
        (SELECT COUNT(*) FROM enrichment en WHERE en.student_id = s.id) AS en_count
    FROM students s
    LEFT JOIN competency_assessments c ON c.id = (
        SELECT MAX(id) FROM competency_assessments WHERE student_id = s.id
    )
    WHERE s.status = "aktif" AND (? = 0 OR s.id = ?)
    ORDER BY FIELD(s.class_name, "XII PPLG 1", "XII PPLG 2", "XII PPLG 3"), s.name', [$ownStudentId, $ownStudentId]);

$remedial = array_values(array_filter($rows, fn($r) => $r['total_score'] !== null && (float)$r['total_score'] < 75));
$enrich = array_values(array_filter($rows, fn($r) => $r['total_score'] !== null && (float)$r['total_score'] >= 85));
$ready = array_values(array_filter($rows, fn($r) => $r['total_score'] !== null && (float)$r['total_score'] >= 75 && (float)$r['total_score'] < 85));
$unscored = array_values(array_filter($rows, fn($r) => $r['total_score'] === null));

$log = can_manage()
    ? db_all('SELECT a.*, u.full_name AS user_name FROM activity_log a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 15')
    : [];
?>
<?php include dirname(__DIR__, 2) . '/includes/header.php'; ?>

<div class="page-head">
    <div>
        <h1><?= can_manage() ? 'Laporan & Tindak Lanjut' : 'Rekomendasi Belajar Saya' ?></h1>
        <p>Rekomendasi remediasi dan pengayaan berdasarkan nilai kompetensi terakhir
            <?= can_manage() ? 'setiap siswa.' : 'Anda.' ?></p>
    </div>
    <div class="btn-group">
        <?php if (can_manage()): ?>
        <a class="btn btn-outline" href="<?= url('modules/reports/export.php') ?>">⬇️ Ekspor CSV</a>
        <?php endif; ?>
        <a class="btn btn-primary" href="<?= url('modules/reports/print.php') ?>" target="_blank">🖨️ Cetak Laporan</a>
    </div>
</div>

<div class="grid grid-4" style="margin-bottom:20px">
    <div class="stat-card"><div class="stat-icon red">⚠️</div><div>
        <div class="stat-value"><?= count($remedial) ?></div><div class="stat-label">Perlu Remediasi (&lt;75)</div></div></div>
    <div class="stat-card"><div class="stat-icon blue">✅</div><div>
        <div class="stat-value"><?= count($ready) ?></div><div class="stat-label">Kompeten (75–84)</div></div></div>
    <div class="stat-card"><div class="stat-icon green">🚀</div><div>
        <div class="stat-value"><?= count($enrich) ?></div><div class="stat-label">Pengayaan (≥85)</div></div></div>
    <div class="stat-card"><div class="stat-icon orange">❔</div><div>
        <div class="stat-value"><?= count($unscored) ?></div><div class="stat-label">Belum Dinilai</div></div></div>
</div>

<?php if (can_manage()): ?>
<div class="card">
    <div class="card-head"><h2>Tetapkan Tindak Lanjut</h2></div>
    <form method="post" action="<?= url('modules/reports/process.php') ?>">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="assign">
        <div class="form-grid">
            <div class="form-group">
                <label>Siswa</label>
                <select name="student_id" required>
                    <option value="">- Pilih siswa -</option>
                    <?php foreach ($rows as $r): ?>
                        <option value="<?= (int)$r['id'] ?>"><?= e($r['name']) ?> — <?= e($r['class_name']) ?>
                            <?= $r['total_score'] !== null ? '(' . fmt_score($r['total_score']) . ')' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jenis Program</label>
                <select name="program">
                    <option value="remediation">Remediasi</option>
                    <option value="enrichment">Pengayaan</option>
                </select>
            </div>
            <div class="form-group">
                <label>Kompetensi / Fokus</label>
                <input type="text" name="competency" placeholder="Contoh: Database & Keamanan" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="rencana">Rencana</option>
                    <option value="proses">Proses</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
            <div class="form-group">
                <label>Batas Waktu</label>
                <input type="date" name="due_date">
            </div>
            <div class="form-group">
                <label>Materi / Bahan</label>
                <input type="text" name="material" placeholder="Contoh: Materi praktik CRUD lokal" required>
            </div>
            <div class="form-group full">
                <label>Tugas</label>
                <textarea name="task" rows="2" required placeholder="Tugas yang harus dikerjakan siswa"></textarea>
            </div>
            <div class="form-group full">
                <label>Catatan</label>
                <textarea name="notes" rows="2"></textarea>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">➕ Tetapkan Program</button>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-head"><h2>Rekomendasi Otomatis</h2></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Siswa</th><th>Nilai</th><th>Rekomendasi</th><th>Program Aktif</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <?php if ($r['total_score'] === null) continue; ?>
                <?php $s = (float)$r['total_score']; ?>
                <tr>
                    <td><strong><?= e($r['name']) ?></strong><br><small><?= e($r['class_name']) ?></small></td>
                    <td><span class="badge <?= score_badge_class($s) ?>"><?= fmt_score($s) ?></span></td>
                    <td>
                        <?php if ($s < 75): ?><span class="badge badge-red">Remediasi</span>
                        <?php elseif ($s >= 85): ?><span class="badge badge-green">Pengayaan</span>
                        <?php else: ?><span class="badge badge-blue">Pertahankan</span><?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int)$r['rem_count'] > 0): ?><span class="badge badge-yellow"><?= (int)$r['rem_count'] ?> remediasi</span> <?php endif; ?>
                        <?php if ((int)$r['en_count'] > 0): ?><span class="badge badge-green"><?= (int)$r['en_count'] ?> pengayaan</span><?php endif; ?>
                        <?php if ((int)$r['rem_count'] === 0 && (int)$r['en_count'] === 0): ?><span class="muted">-</span><?php endif; ?>
                    </td>
                    <td><a class="btn btn-light btn-sm" href="<?= url('modules/students/detail.php?id=' . (int)$r['id']) ?>">Detail</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($log): ?>
<div class="card">
    <div class="card-head"><h3>Aktivitas Terbaru</h3></div>
    <?php foreach ($log as $l): ?>
        <div style="padding:8px 0;border-bottom:1px solid var(--line);font-size:.85rem">
            <strong><?= e($l['action']) ?></strong> — <?= e($l['description']) ?>
            <br><small class="muted"><?= e($l['user_name'] ?? 'Sistem') ?> · <?= e(format_date($l['created_at'], true)) ?></small>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
