<?php

declare(strict_types=1);

use Morgo\Http\Admin\AuthController;
use Morgo\Http\Admin\DashboardController;
use Morgo\Http\Admin\PageController;
use Morgo\Http\Admin\MediaController;
use Morgo\Http\Admin\MenuController;
use Morgo\Http\Admin\WidgetController;
use Morgo\Http\Admin\UserController;
use Morgo\Http\Admin\PluginController;
use Morgo\Http\Admin\ThemeController;
use Morgo\Http\Admin\SettingsController;
use Morgo\Http\Frontend\PageController as FrontendPageController;
use Morgo\Http\Middleware\AuthMiddleware;
use Morgo\Http\Middleware\CsrfMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    // Instalační wizard (dostupný pouze pokud není nainstalováno)
    $app->group('/install', function (RouteCollectorProxy $group) {
        $group->get('[/]', [\Morgo\Http\Install\InstallController::class, 'index']);
        $group->get('/{step}', [\Morgo\Http\Install\InstallController::class, 'step']);
        $group->post('/{step}', [\Morgo\Http\Install\InstallController::class, 'process']);
        $group->post('/test-db', [\Morgo\Http\Install\InstallController::class, 'testDb']);
    });

    // Admin — přihlašování (bez AuthMiddleware)
    $adminPrefix = '/' . trim(config('app.admin_prefix', 'admin'), '/');
    $app->get($adminPrefix . '/login', [AuthController::class, 'loginForm']);
    $app->post($adminPrefix . '/login', [AuthController::class, 'login']);
    $app->post($adminPrefix . '/logout', [AuthController::class, 'logout']);

    // Admin — chráněné routy
    $app->group($adminPrefix, function (RouteCollectorProxy $group) {
        $group->get('[/]', [DashboardController::class, 'index']);

        // Stránky
        $group->get('/pages', [PageController::class, 'index']);
        $group->get('/pages/new', [PageController::class, 'create']);
        $group->post('/pages', [PageController::class, 'store']);
        $group->get('/pages/{id:[0-9]+}/edit', [PageController::class, 'edit']);
        $group->post('/pages/{id:[0-9]+}', [PageController::class, 'update']);
        $group->post('/pages/{id:[0-9]+}/delete', [PageController::class, 'destroy']);
        $group->post('/pages/draft', [PageController::class, 'draft']);

        // Média
        $group->get('/media', [MediaController::class, 'index']);
        $group->post('/media', [MediaController::class, 'store']);
        $group->post('/media/{id:[0-9]+}/delete', [MediaController::class, 'destroy']);
        $group->get('/media/picker', [MediaController::class, 'picker']);

        // Menu
        $group->get('/menus', [MenuController::class, 'index']);
        $group->get('/menus/new', [MenuController::class, 'create']);
        $group->post('/menus', [MenuController::class, 'store']);
        $group->get('/menus/{id:[0-9]+}/edit', [MenuController::class, 'edit']);
        $group->post('/menus/{id:[0-9]+}', [MenuController::class, 'update']);
        $group->post('/menus/{id:[0-9]+}/delete', [MenuController::class, 'destroy']);

        // Widgety
        $group->get('/widgets', [WidgetController::class, 'index']);
        $group->post('/widgets', [WidgetController::class, 'store']);
        $group->post('/widgets/{id:[0-9]+}', [WidgetController::class, 'update']);
        $group->post('/widgets/{id:[0-9]+}/delete', [WidgetController::class, 'destroy']);
        $group->post('/widgets/reorder', [WidgetController::class, 'reorder']);

        // Uživatelé
        $group->get('/users', [UserController::class, 'index']);
        $group->get('/users/new', [UserController::class, 'create']);
        $group->post('/users', [UserController::class, 'store']);
        $group->get('/users/{id:[0-9]+}/edit', [UserController::class, 'edit']);
        $group->post('/users/{id:[0-9]+}', [UserController::class, 'update']);
        $group->post('/users/{id:[0-9]+}/delete', [UserController::class, 'destroy']);

        // Pluginy
        $group->get('/plugins', [PluginController::class, 'index']);
        $group->post('/plugins/{slug}/activate', [PluginController::class, 'activate']);
        $group->post('/plugins/{slug}/deactivate', [PluginController::class, 'deactivate']);

        // Témata
        $group->get('/themes', [ThemeController::class, 'index']);
        $group->post('/themes/{slug}/activate', [ThemeController::class, 'activate']);

        // Nastavení
        $group->get('/settings', [SettingsController::class, 'index']);
        $group->post('/settings', [SettingsController::class, 'update']);

    })->add(CsrfMiddleware::class)->add(AuthMiddleware::class);

    // Pluginy mohou registrovat vlastní routy
    sp_do_action('routes.register', $app);

    // Frontend — musí být jako poslední
    $app->get('/', [FrontendPageController::class, 'home']);
    $app->get('/{slug:.*}', [FrontendPageController::class, 'page']);
};
