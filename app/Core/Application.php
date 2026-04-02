<?php

declare(strict_types=1);

namespace Morgo\Core;

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;

class Application
{
    private SlimApp $app;

    public function create(): SlimApp
    {
        // Načti .env soubor pokud existuje
        $this->loadEnv();

        // Sestavit DI container
        $builder = new ContainerBuilder();

        if (config('app.env') === 'production') {
            $builder->enableCompilation(BASE_PATH . '/storage/cache/di');
        }

        $containerDefinitions = require BASE_PATH . '/config/container.php';
        $containerDefinitions($builder);

        $container = $builder->build();

        // Uložit container do globálního prostoru pro helper funkce
        global $morgoContainer;
        $morgoContainer = $container;

        // Inicializovat Eloquent (spustit factory)
        $container->get(Capsule::class);

        // Vytvořit Slim aplikaci
        AppFactory::setContainer($container);
        $this->app = AppFactory::create();

        // Error handling
        $this->setupErrorHandling();

        // Middleware
        $this->registerMiddleware();

        // Routy
        $routeDefinitions = require BASE_PATH . '/config/routes.php';
        $routeDefinitions($this->app);

        // Spustit hook app.boot
        sp_do_action('app.boot', $this->app);

        return $this->app;
    }

    private function loadEnv(): void
    {
        $envFile = BASE_PATH . '/.env';
        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            if (!isset($_ENV[$key])) {
                $_ENV[$key]     = $value;
                $_SERVER[$key]  = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    private function setupErrorHandling(): void
    {
        $errorMiddleware = $this->app->addErrorMiddleware(
            displayErrorDetails: config('app.debug', false),
            logErrors: true,
            logErrorDetails: config('app.debug', false)
        );

        // Vlastní error handler pro produkci
        if (!config('app.debug')) {
            $errorMiddleware->setDefaultErrorHandler(
                new \Morgo\Http\Middleware\ErrorHandler($this->app->getCallableResolver(), $this->app->getResponseFactory())
            );
        }
    }

    private function registerMiddleware(): void
    {
        $this->app->addRoutingMiddleware();
        $this->app->add(new \Morgo\Http\Middleware\SecurityHeadersMiddleware());

        // Method override (_method POST field pro DELETE/PUT)
        $this->app->add(new \Morgo\Http\Middleware\MethodOverrideMiddleware());
    }
}
