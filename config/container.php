<?php

declare(strict_types=1);

use Slim\Views\PhpRenderer;

return [
    PhpRenderer::class => fn() => new PhpRenderer(
        dirname(__DIR__) . '/templates',
        [],
        'layout.php',
    ),
];
