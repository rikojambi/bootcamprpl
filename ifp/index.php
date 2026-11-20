<?php
/** ifp/index.php - Beranda mode presentasi IFP. */
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/auth/auth.php';
require_once __DIR__ . '/_layout.php';

require_login();
$page_title = 'Mode Presentasi IFP';

$stats = [
    'Sesi Bootcamp'  => (int) db_value('SELECT COUNT(*) FROM bootcamp_sessions'),
    'Jobsheet'       => (int) db_value('SELECT COUNT(*) FROM jobsheets'),
    'Tantangan'      => (int) db_value('SELECT COUNT(*) FROM coding_challenges'),
    'Pertanyaan'     => (int) db_value('SELECT COUNT(*) FROM question_bank'),
];

$menu = [
    ['materi.php?no=1',   '📘', 'Materi Bootcamp', '16 pertemuan, 4 minggu'],
    ['simulasi.php',      '🔄', 'Simulasi Arsitektur', 'Frontend → Backend → Database'],
    ['seminar.php',       '🎤', 'Seminar Hasil PKL', 'Bedah kode + penilaian'],
    ['challenges.php',    '⚡', 'Tantangan Live Coding', '15 tantangan bertingkat'],
];
ifp_header('MODE PRESENTASI IFP', SCHOOL_NAME . ' · Layar Sentuh 1920x1080 · Offline Lokal');
?>
<main class="ifp-body">
    <div class="ifp-hero">
        <h2>BOOTCAMP PRE-UKK FULLSTACK DEVELOPER</h2>
        <p>Media presentasi guru di Interactive Flat Panel (IFP). Font besar, tombol sentuh minimal 60px, dan navigasi keyboard untuk perpindahan slide yang cepat.</p>
    </div>

    <div class="ifp-grid cols-4" style="margin-bottom:26px">
        <?php foreach ($stats as $label => $value): ?>
            <div class="ifp-card text-center">
                <div style="font-size:2.6rem;font-weight:900;color:var(--ifp-primary);line-height:1"><?= $value ?></div>
                <div class="muted"><?= e($label) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="ifp-section-title">Pilih Materi Presentasi</div>
    <div class="ifp-grid cols-4">
        <?php foreach ($menu as [$link, $icon, $label, $desc]): ?>
            <a class="ifp-menu" href="<?= url('ifp/' . $link) ?>">
                <span class="ico"><?= $icon ?></span>
                <?= e($label) ?>
                <small><?= e($desc) ?></small>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="ifp-card" style="margin-top:26px">
        <h3>Panduan Singkat Guru</h3>
        <ul style="padding-left:24px">
            <li>Tekan <strong>F11</strong> atau tombol <strong>Layar Penuh</strong> untuk memaksimalkan layar.</li>
            <li>Gunakan tombol <strong>A−</strong> dan <strong>A＋</strong> untuk menyesuaikan ukuran teks sesuai jarak pandang siswa.</li>
            <li>Pada halaman materi, gunakan tombol <strong>←</strong> / <strong>→</strong> atau keyboard untuk berpindah slide.</li>
            <li>Semua konten tersimpan lokal. Tidak memerlukan koneksi internet.</li>
        </ul>
    </div>
</main>
<?php ifp_footer(); ?>
