<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Core\HookManager;
use Morgo\Core\I18n;
use Morgo\Domain\Page\PageRepositoryInterface;
use Morgo\Domain\User\UserRepositoryInterface;
use Morgo\Domain\Media\MediaRepositoryInterface;
use Morgo\Infrastructure\Persistence\EloquentPageRepository;
use Morgo\Infrastructure\Persistence\EloquentUserRepository;
use Morgo\Infrastructure\Persistence\EloquentMediaRepository;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $builder): void {
    $builder->addDefinitions([
        // HookManager — singleton
        HookManager::class => \DI\create(HookManager::class),

        // I18n
        I18n::class => \DI\factory(function () {
            $locale = $_ENV['APP_LOCALE'] ?? 'cs_CZ';
            $i18n   = new I18n($locale);
            // Načti core překlady
            $langPath = BASE_PATH . '/lang';
            $i18n->loadDomain('morgocms', $langPath);
            return $i18n;
        }),

        // Eloquent Capsule (Databáze)
        Capsule::class => \DI\factory(function () {
            $capsule = new Capsule();
            $dbConfig = config('app.db');

            if ($dbConfig['driver'] === 'sqlite') {
                $capsule->addConnection([
                    'driver'   => 'sqlite',
                    'database' => $dbConfig['database'],
                    'prefix'   => '',
                ]);
            } else {
                $capsule->addConnection([
                    'driver'    => $dbConfig['driver'],
                    'host'      => $dbConfig['host'],
                    'port'      => $dbConfig['port'],
                    'database'  => $dbConfig['database'],
                    'username'  => $dbConfig['username'],
                    'password'  => $dbConfig['password'],
                    'charset'   => $dbConfig['charset'],
                    'collation' => $dbConfig['collation'],
                    'prefix'    => $dbConfig['prefix'],
                ]);
            }

            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            return $capsule;
        }),

        // PSR-3 Logger — Monolog
        LoggerInterface::class => \DI\factory(function () {
            $logger  = new \Monolog\Logger('morgocms');
            $level   = config('app.debug') ? \Monolog\Level::Debug : \Monolog\Level::Warning;
            $logFile = BASE_PATH . '/storage/logs/app.log';

            if (config('app.env') === 'production') {
                $handler = new \Monolog\Handler\RotatingFileHandler($logFile, 30, $level);
            } else {
                $handler = new \Monolog\Handler\StreamHandler($logFile, $level);
            }

            $logger->pushHandler($handler);
            return $logger;
        }),

        // Repository bindings (interface → implementace)
        PageRepositoryInterface::class  => \DI\autowire(EloquentPageRepository::class),
        UserRepositoryInterface::class  => \DI\autowire(EloquentUserRepository::class),
        MediaRepositoryInterface::class => \DI\autowire(EloquentMediaRepository::class),

        // BlockRenderer
        \Morgo\Services\BlockRenderer::class => \DI\autowire(\Morgo\Services\BlockRenderer::class),

        // ThemeEngine
        \Morgo\Core\ThemeEngine::class => \DI\create(\Morgo\Core\ThemeEngine::class),

        // Frontend controllers
        \Morgo\Http\Frontend\PageController::class => \DI\autowire(\Morgo\Http\Frontend\PageController::class),
    ]);
};
