<?php
/** ifp/_layout.php - Kerangka tampilan mode presentasi IFP (bukan halaman mandiri). */
function ifp_header(string $title, string $subtitle = ''): void
{
    ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title><?= e($title) ?> — Mode IFP</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ifp.css') ?>">
</head>
<body class="ifp">
<header class="ifp-top">
    <div class="brand">
        <div class="brand-mark">BF</div>
        <div>
            <h1><?= e($title) ?></h1>
            <p><?= e($subtitle ?: (SCHOOL_NAME . ' · ' . PROGRAM_NAME)) ?></p>
        </div>
    </div>
    <div class="ifp-actions">
        <button class="ifp-btn small" onclick="changeFontSize(-1)" title="Perkecil teks">A−</button>
        <button class="ifp-btn small" onclick="changeFontSize(1)" title="Perbesar teks">A＋</button>
        <a class="ifp-btn small" href="<?= url('index.php') ?>">← Kembali</a>
        <button class="ifp-btn primary" onclick="toggleFullscreen()">⛶ Layar Penuh</button>
    </div>
</header>
    <?php
}

function ifp_footer(string $left = '', string $right = ''): void
{
    ?>
<footer class="ifp-footer">
    <div class="slide-info"><?= $left ?: ('<strong>' . e(APP_SHORT) . '</strong> · Mode Presentasi IFP · ' . e(SCHOOL_NAME)) ?></div>
    <div class="ifp-actions"><?= $right ?></div>
</footer>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/ifp.js') ?>"></script>
</body>
</html>
    <?php
}
