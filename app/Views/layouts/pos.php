<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('pos.title') ?> - <?= e(config('app.name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= assetUrl('css/style.css') ?>" rel="stylesheet">
    <link href="<?= assetUrl('css/pos.css') ?>" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark px-3">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= baseUrl('dashboard') ?>" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left"></i> <?= __('pos.exit') ?>
            </a>
            <span class="navbar-brand mb-0 h6"><?= e(config('app.name')) ?> — <?= __('pos.title') ?></span>
        </div>
        <div class="d-flex align-items-center gap-2 text-white small">
            <?php foreach (\App\Lang\Lang::available() as $code => $name): ?>
                <a href="<?= baseUrl('lang/' . $code) ?>" class="text-white text-decoration-none <?= \App\Lang\Lang::locale() === $code ? 'fw-bold' : 'opacity-75' ?>"><?= $name ?></a>
            <?php endforeach; ?>
            <span class="text-secondary">|</span>
            <span><?= e(\App\Core\Session::get('outlet_name', '')) ?></span>
            <span class="text-secondary">|</span>
            <span><?= e(\App\Core\Session::get('user_name', '')) ?></span>
        </div>
    </nav>

    <?php if ($msg = flash('error')): ?>
        <div class="alert alert-danger rounded-0 mb-0"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success rounded-0 mb-0"><?= e($msg) ?></div>
    <?php endif; ?>

    <?= $content ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/dist/html5-qrcode.min.js"></script>
    <script src="<?= assetUrl('js/pos.js') ?>"></script>
</body>
</html>
