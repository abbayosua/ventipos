<?php

// Redirect to installer if not configured
if (!file_exists(__DIR__ . '/../config.php') || !file_exists(__DIR__ . '/../install.lock')) {
    header('Location: install.php');
    exit;
}

$config = require __DIR__ . '/../config.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

require_once __DIR__ . '/../app/Helpers/helpers.php';

use App\Core\App;

$app = new App($config);
$app->run();
