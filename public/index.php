<?php

declare(strict_types=1);

use App\Controller\HomeController;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

$view = new PhpRenderer(dirname(__DIR__) . '/templates', [], 'layout.php');

$app->get('/', [new HomeController($view), 'index']);

$app->run();
