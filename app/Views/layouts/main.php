<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? __('dashboard.title')) ?> - <?= e(config('app.name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= assetUrl('css/style.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar bg-dark text-white vh-100 p-3" style="width: 250px; position: fixed;">
            <h5 class="text-center mb-4"><?= e(config('app.name')) ?></h5>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= baseUrl('dashboard') ?>">
                        <i class="bi bi-speedometer2"></i> <?= __('nav.dashboard') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= baseUrl('pos') ?>">
                        <i class="bi bi-cart"></i> <?= __('nav.pos') ?>
                    </a>
                </li>
                <hr class="text-secondary">
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= baseUrl('products') ?>">
                        <i class="bi bi-box"></i> <?= __('nav.products') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= baseUrl('categories') ?>">
                        <i class="bi bi-tags"></i> <?= __('nav.categories') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= baseUrl('stock') ?>">
                        <i class="bi bi-boxes"></i> <?= __('nav.stock') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= baseUrl('customers') ?>">
                        <i class="bi bi-people"></i> <?= __('nav.customers') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= baseUrl('suppliers') ?>">
                        <i class="bi bi-truck"></i> <?= __('nav.suppliers') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= baseUrl('sales') ?>">
                        <i class="bi bi-receipt"></i> <?= __('nav.sales') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= baseUrl('expenses') ?>">
                        <i class="bi bi-wallet2"></i> <?= __('nav.expenses') ?>
                    </a>
                </li>
                <hr class="text-secondary">
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= baseUrl('reports/daily') ?>">
                        <i class="bi bi-graph-up"></i> <?= __('nav.reports') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= baseUrl('settings') ?>">
                        <i class="bi bi-gear"></i> <?= __('nav.settings') ?>
                    </a>
                </li>
                <hr class="text-secondary">
                <li class="nav-item">
                    <a class="nav-link text-danger" href="<?= baseUrl('logout') ?>">
                        <i class="bi bi-box-arrow-right"></i> <?= __('nav.logout') ?>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1" style="margin-left: 250px;">
            <!-- Top Bar -->
            <nav class="navbar navbar-light bg-white shadow-sm px-4">
                <span class="navbar-text">
                    <?= e(\App\Core\Session::get('company_name', '')) ?>
                    /
                    <?= e(\App\Core\Session::get('outlet_name', '')) ?>
                </span>
                <div class="d-flex align-items-center gap-3">
                    <?php foreach (\App\Lang\Lang::available() as $code => $name): ?>
                        <a href="<?= baseUrl('lang/' . $code) ?>" class="btn btn-sm <?= \App\Lang\Lang::locale() === $code ? 'btn-dark' : 'btn-outline-secondary' ?>"><?= $name ?></a>
                    <?php endforeach; ?>
                    <span class="text-muted"><?= e(\App\Core\Session::get('user_name', '')) ?></span>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="p-4">
                <?php if ($msg = flash('error')): ?>
                    <div class="alert alert-danger"><?= e($msg) ?></div>
                <?php endif; ?>
                <?php if ($msg = flash('success')): ?>
                    <div class="alert alert-success"><?= e($msg) ?></div>
                <?php endif; ?>
                <?= $content ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/dist/html5-qrcode.min.js"></script>
    <script src="<?= assetUrl('js/app.js') ?>"></script>
</body>
</html>
