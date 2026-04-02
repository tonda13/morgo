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

        // Načíst téma (functions.php) — nutné pro registraci nav menu lokací, CSS/JS, hook init
        $container->get(ThemeEngine::class)->load();

        // Registrovat menu renderer
        $this->registerMenuRenderer($container);

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

    private function registerMenuRenderer(\DI\Container $container): void
    {
        $capsule = $container->get(\Illuminate\Database\Capsule\Manager::class);

        // Pro každou registrovanou lokaci zaregistruj filter který načte menu dle slugu
        sp_add_filter('render_menu', function (string $html, int $menuId, $capsule): string {
            $items = $capsule->table('menu_items')
                ->where('menu_id', $menuId)
                ->whereNull('parent_id')
                ->orderBy('menu_order')
                ->get();

            if ($items->isEmpty()) {
                return $html;
            }

            $out = '<ul class="nav-menu">';
            foreach ($items as $item) {
                $url   = $item->url ?: ($item->page_id
                    ? site_url($capsule->table('pages')->where('id', $item->page_id)->value('slug') ?? '#')
                    : '#');
                $label = htmlspecialchars($item->label, ENT_QUOTES);

                // Podpoložky
                $children = $capsule->table('menu_items')
                    ->where('menu_id', $menuId)
                    ->where('parent_id', $item->id)
                    ->orderBy('menu_order')
                    ->get();

                $out .= '<li class="nav-item">';
                $out .= '<a href="' . esc_url($url) . '">' . $label . '</a>';

                if ($children->isNotEmpty()) {
                    $out .= '<ul class="sub-menu">';
                    foreach ($children as $child) {
                        $childUrl   = $child->url ?: ($child->page_id
                            ? site_url($capsule->table('pages')->where('id', $child->page_id)->value('slug') ?? '#')
                            : '#');
                        $childLabel = htmlspecialchars($child->label, ENT_QUOTES);
                        $out .= '<li class="nav-item"><a href="' . esc_url($childUrl) . '">' . $childLabel . '</a></li>';
                    }
                    $out .= '</ul>';
                }

                $out .= '</li>';
            }
            $out .= '</ul>';

            return $out;
        }, 10, 2);

        // Zaregistruj filter pro každou lokaci — načte menu dle option menu_location_{location}
        $registeredLocations = ['primary', 'footer'];
        foreach ($registeredLocations as $location) {
            sp_add_filter('render_menu_' . $location, function (string $html) use ($capsule, $location): string {
                $menuId = (int) get_option('menu_location_' . $location, 0);
                if ($menuId <= 0) {
                    return $html;
                }
                return sp_apply_filters('render_menu', $html, $menuId, $capsule);
            });
        }
    }

    private function registerMiddleware(): void
    {
        $this->app->addRoutingMiddleware();
        $this->app->add(new \Morgo\Http\Middleware\SecurityHeadersMiddleware());

        // Method override (_method POST field pro DELETE/PUT)
        $this->app->add(new \Morgo\Http\Middleware\MethodOverrideMiddleware());

        // Přesměrování na /install pokud aplikace není nainstalována (nejvnější vrstva)
        $this->app->add(new \Morgo\Http\Middleware\InstallCheckMiddleware(
            $this->app->getResponseFactory()
        ));
    }
}
