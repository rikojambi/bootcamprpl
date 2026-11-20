<?php
/** ifp/materi.php - Presentasi materi bootcamp per pertemuan (slide per bagian). */
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/auth/auth.php';
require_once __DIR__ . '/_layout.php';

require_login();

$no = max(1, min(16, (int) query('no', 1)));
$session = db_one('SELECT * FROM bootcamp_sessions WHERE session_no = ?', [$no]);
if (!$session) redirect('ifp/index.php');

$slides = [
    ['Tujuan Pembelajaran', nl2br(e($session['objectives']))],
    ['Materi Inti', nl2br(e($session['core_material']))],
    ['Penjelasan Konsep', nl2br(e($session['concept']))],
    ['Diagram / Alur', '<div class="diagram-text">' . e($session['diagram'] ?: '-') . '</div>'],
    ['Contoh Kode', '<pre>' . e($session['code_example'] ?: '// Belum ada contoh kode.') . '</pre>'],
    ['Demonstrasi Guru', nl2br(e($session['demo']))],
    ['Latihan & Pertanyaan', nl2br(e($session['exercise'])) . '<h3>Pertanyaan Pemahaman</h3><ol>' .
        implode('', array_map(fn($q) => '<li>' . e($q) . '</li>',
            array_filter(array_map('trim', explode('|', (string) $session['questions']))))) . '</ol>'],
    ['Evaluasi & Refleksi', '<h3>Evaluasi</h3>' . nl2br(e($session['evaluation'])) .
        '<h3>Refleksi Siswa</h3>' . nl2br(e($session['reflection']))],
];

$slide = max(0, min(count($slides) - 1, (int) query('slide', 0)));
$title = 'Pertemuan ' . $no . ' — ' . $session['title'];
ifp_header('MATERI BOOTCAMP', $title . ' · Minggu ' . (int) $session['week_no']);
?>

<main class="ifp-body wide">
    <div class="ifp-slide" id="ifp-slide">
        <span class="ifp-badge">SLIDE <?= $slide + 1 ?> / <?= count($slides) ?></span>
        <h2><?= e($slides[$slide][0]) ?></h2>
        <div><?= $slides[$slide][1] ?></div>
    </div>

    <div style="display:flex;justify-content:space-between;gap:14px;margin-top:22px;flex-wrap:wrap">
        <div class="btn-group">
            <a class="ifp-btn" href="<?= url('ifp/index.php') ?>">🏠 Beranda</a>
            <a class="ifp-btn" href="<?= url('ifp/materi.php?no=' . max(1, $no - 1)) ?>">← Pertemuan Sebelumnya</a>
            <a class="ifp-btn" href="<?= url('ifp/materi.php?no=' . min(16, $no + 1)) ?>">Pertemuan Berikutnya →</a>
        </div>
        <div class="btn-group">
            <?php if ($slide > 0): ?>
                <a class="ifp-btn primary" id="prev-slide" href="<?= url('ifp/materi.php?no=' . $no . '&slide=' . ($slide - 1)) ?>">← Slide Sebelumnya</a>
            <?php endif; ?>
            <?php if ($slide < count($slides) - 1): ?>
                <a class="ifp-btn accent" id="next-slide" href="<?= url('ifp/materi.php?no=' . $no . '&slide=' . ($slide + 1)) ?>">Slide Berikutnya →</a>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php ifp_footer('<strong>' . e($title) . '</strong> · Slide ' . ($slide + 1) . ' dari ' . count($slides)); ?>
