<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Core\HookManager;
use Morgo\Core\I18n;
use Morgo\Domain\Page\PageRepositoryInterface;
use Morgo\Domain\User\UserRepositoryInterface;
use Morgo\Domain\Media\MediaRepositoryInterface;
use Morgo\Domain\Menu\MenuRepositoryInterface;
use Morgo\Infrastructure\Persistence\EloquentPageRepository;
use Morgo\Infrastructure\Persistence\EloquentUserRepository;
use Morgo\Infrastructure\Persistence\EloquentMediaRepository;
use Morgo\Infrastructure\Persistence\EloquentMenuRepository;
use Morgo\Infrastructure\Storage\LocalMediaStorage;
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

        // PSR-3 Logger — MonologLogger wrapper
        \Morgo\Infrastructure\Logging\MonologLogger::class => \DI\factory(function () {
            $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
            return new \Morgo\Infrastructure\Logging\MonologLogger('morgocms', '', $debug);
        }),

        LoggerInterface::class => \DI\factory(function () {
            $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
            return new \Morgo\Infrastructure\Logging\MonologLogger('morgocms', '', $debug);
        }),

        // Repository bindings (interface → implementace)
        PageRepositoryInterface::class  => \DI\autowire(EloquentPageRepository::class),
        UserRepositoryInterface::class  => \DI\autowire(EloquentUserRepository::class),
        MediaRepositoryInterface::class => \DI\autowire(EloquentMediaRepository::class),
        MenuRepositoryInterface::class  => \DI\autowire(EloquentMenuRepository::class),

        // Storage
        LocalMediaStorage::class => \DI\create(LocalMediaStorage::class),

        // BlockRenderer
        \Morgo\Services\BlockRenderer::class => \DI\autowire(\Morgo\Services\BlockRenderer::class),

        // ThemeEngine
        \Morgo\Core\ThemeEngine::class => \DI\create(\Morgo\Core\ThemeEngine::class),

        // Frontend controllers
        \Morgo\Http\Frontend\PageController::class => \DI\autowire(\Morgo\Http\Frontend\PageController::class),

        // Plugin systém
        \Morgo\Core\PluginLoader::class => \DI\autowire(\Morgo\Core\PluginLoader::class),

        // CLI příkazy
        \Morgo\Console\Commands\MigrateCommand::class        => \DI\autowire(),
        \Morgo\Console\Commands\MigrateRollbackCommand::class => \DI\autowire(),
        \Morgo\Console\Commands\MigrateStatusCommand::class  => \DI\autowire(),
        \Morgo\Console\Commands\UserCreateCommand::class     => \DI\autowire(),
        \Morgo\Console\Commands\ThemeListCommand::class      => \DI\autowire(),
        \Morgo\Console\Commands\ThemeActivateCommand::class  => \DI\autowire(),
        \Morgo\Console\Commands\PluginListCommand::class     => \DI\autowire(),
        \Morgo\Console\Commands\PluginEnableCommand::class   => \DI\autowire(),
        \Morgo\Console\Commands\PluginDisableCommand::class  => \DI\autowire(),
        \Morgo\Console\Commands\CacheClearCommand::class     => \DI\create(),
    ]);
};
