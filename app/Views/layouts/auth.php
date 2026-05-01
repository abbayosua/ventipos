<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('auth.login_title', ['app' => config('app.name')]) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= assetUrl('css/style.css') ?>" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="text-end mt-2">
            <?php foreach (\App\Lang\Lang::available() as $code => $name): ?>
                <a href="<?= baseUrl('lang/' . $code) ?>" class="btn btn-sm <?= \App\Lang\Lang::locale() === $code ? 'btn-dark' : 'btn-outline-secondary' ?>"><?= $name ?></a>
            <?php endforeach; ?>
        </div>
        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger mt-3"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success mt-3"><?= e($msg) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
