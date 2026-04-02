<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

// Nastavení testovacího prostředí
$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_DEBUG'] = 'true';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
