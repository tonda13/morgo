<?php

declare(strict_types=1);

define('MORGO_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

// Autoloader
require BASE_PATH . '/vendor/autoload.php';

// Bootstrap aplikace
$app = (new Morgo\Core\Application())->create();

// Spustit aplikaci
$app->run();
