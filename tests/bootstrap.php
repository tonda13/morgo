<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

// Nastavení testovacího prostředí
$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_DEBUG'] = 'true';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
$_ENV['APP_KEY'] = 'test-key-32-chars-long-for-test!!';
$_ENV['APP_URL'] = 'http://localhost';

// Definice konstant potřebných pro aplikaci
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
