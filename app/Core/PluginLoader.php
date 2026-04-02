<?php

declare(strict_types=1);

namespace Morgo\Core;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Log\LoggerInterface;

class PluginLoader
{
    /** @var PluginInterface[] */
    private array $plugins = [];

    public function __construct(
        private readonly Capsule $capsule,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Načte všechny aktivní pluginy definované v composer.json extra.morgocms-plugins.
     */
    public function load(): void
    {
        $composerJson = BASE_PATH . '/composer.json';
        if (!file_exists($composerJson)) {
            return;
        }

        $composer      = json_decode(file_get_contents($composerJson), true);
        $pluginClasses = $composer['extra']['morgocms-plugins'] ?? [];

        // Filtrovat pouze aktivní pluginy (uložené v options)
        $activePlugins = $this->getActivePlugins();

        foreach ($pluginClasses as $class) {
            if (!class_exists($class)) {
                $this->logger->warning("Plugin class not found: {$class}");
                continue;
            }

            $slug = $class::getSlug();
            if (!in_array($slug, $activePlugins, true)) {
                continue;
            }

            /** @var PluginInterface $plugin */
            $plugin = new $class();
            $plugin->register();
            $this->plugins[$slug] = $plugin;
        }

        sp_do_action('plugins.loaded');

        // Boot všech pluginů (po registraci všech)
        foreach ($this->plugins as $plugin) {
            $plugin->boot();
        }
    }

    public function activate(string $slug): bool
    {
        $class = $this->findClass($slug);
        if ($class === null) {
            return false;
        }

        $activePlugins = $this->getActivePlugins();
        if (in_array($slug, $activePlugins, true)) {
            return true;
        }

        /** @var PluginInterface $plugin */
        $plugin = new $class();

        // Spustit migrace pluginu
        $migrationsPath = $plugin->getMigrationsPath();
        if ($migrationsPath) {
            $manager = new Migration\MigrationManager($this->capsule, $this->logger);
            $manager->runPlugin($slug, $migrationsPath);
        }

        $plugin->activate();

        $activePlugins[] = $slug;
        $this->updateActivePlugins($activePlugins);

        $this->logger->info("Plugin activated: {$slug}");
        return true;
    }

    public function deactivate(string $slug): bool
    {
        $class = $this->findClass($slug);
        if ($class === null) {
            return false;
        }

        $activePlugins = $this->getActivePlugins();
        if (!in_array($slug, $activePlugins, true)) {
            return true;
        }

        /** @var PluginInterface $plugin */
        $plugin = new $class();
        $plugin->deactivate();

        $activePlugins = array_filter($activePlugins, fn($s) => $s !== $slug);
        $this->updateActivePlugins(array_values($activePlugins));

        $this->logger->info("Plugin deactivated: {$slug}");
        return true;
    }

    /** @return array<string, array{slug: string, name: string, version: string, active: bool}> */
    public function getAllPlugins(): array
    {
        $composerJson = BASE_PATH . '/composer.json';
        if (!file_exists($composerJson)) {
            return [];
        }

        $composer      = json_decode(file_get_contents($composerJson), true);
        $pluginClasses = $composer['extra']['morgocms-plugins'] ?? [];
        $activePlugins = $this->getActivePlugins();

        $result = [];
        foreach ($pluginClasses as $class) {
            if (!class_exists($class)) {
                continue;
            }
            $slug          = $class::getSlug();
            $result[$slug] = [
                'slug'    => $slug,
                'name'    => $class::getName(),
                'version' => $class::getVersion(),
                'active'  => in_array($slug, $activePlugins, true),
                'class'   => $class,
            ];
        }

        return $result;
    }

    public function getActivePlugins(): array
    {
        try {
            $value = $this->capsule->table('options')
                ->where('option_key', 'active_plugins')
                ->value('option_value');
            return $value ? json_decode($value, true) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function updateActivePlugins(array $plugins): void
    {
        $this->capsule->table('options')->updateOrInsert(
            ['option_key' => 'active_plugins'],
            ['option_value' => json_encode(array_values($plugins))]
        );
    }

    private function findClass(string $slug): ?string
    {
        $composerJson = BASE_PATH . '/composer.json';
        if (!file_exists($composerJson)) {
            return null;
        }

        $composer      = json_decode(file_get_contents($composerJson), true);
        $pluginClasses = $composer['extra']['morgocms-plugins'] ?? [];

        foreach ($pluginClasses as $class) {
            if (class_exists($class) && $class::getSlug() === $slug) {
                return $class;
            }
        }

        return null;
    }
}
