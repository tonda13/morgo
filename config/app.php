<?php

declare(strict_types=1);

return [
    'name'         => $_ENV['APP_NAME']    ?? 'Morgo',
    'env'          => $_ENV['APP_ENV']     ?? 'production',
    'debug'        => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'          => $_ENV['APP_URL']     ?? 'http://localhost',
    'admin_prefix' => $_ENV['ADMIN_PREFIX'] ?? 'admin',
    'installed'    => false, // Přepíše se na true po instalaci

    'db' => [
        'driver'    => $_ENV['DB_CONNECTION'] ?? 'mysql',
        'host'      => $_ENV['DB_HOST']       ?? '127.0.0.1',
        'port'      => (int) ($_ENV['DB_PORT'] ?? 3306),
        'database'  => $_ENV['DB_DATABASE']   ?? 'morgocms',
        'username'  => $_ENV['DB_USERNAME']   ?? 'root',
        'password'  => $_ENV['DB_PASSWORD']   ?? '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
    ],

    'session' => [
        'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 120),
        'secure'   => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
    ],

    'upload' => [
        'max_size'  => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760),
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
        ],
    ],
];
