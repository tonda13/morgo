<?php

declare(strict_types=1);

namespace Morgo\Core;

class ThemeEngine
{
    private string $themeSlug;
    private ?string $parentSlug;
    private string $themeDir;
    private ?string $parentDir;

    public function __construct()
    {
        $this->themeSlug  = get_option('active_theme') ?: 'default';
        $themeMeta        = $this->loadMeta($this->themeSlug);
        $this->parentSlug = $themeMeta['parent'] ?? null;

        $this->themeDir  = BASE_PATH . '/themes/' . $this->themeSlug;
        $this->parentDir = $this->parentSlug
            ? BASE_PATH . '/themes/' . $this->parentSlug
            : null;
    }

    public function load(): void
    {
        // Načíst functions.php — parent první, pak child
        if ($this->parentDir) {
            $parentFunctions = $this->parentDir . '/functions.php';
            if (file_exists($parentFunctions)) {
                require_once $parentFunctions;
            }
        }

        $themeFunctions = $this->themeDir . '/functions.php';
        if (file_exists($themeFunctions)) {
            require_once $themeFunctions;
        }

        sp_do_action('theme.loaded');
    }

    /**
     * Najde správný soubor šablony podle hierarchie.
     * 1. page-{slug}.php
     * 2. page-{id}.php
     * 3. Pojmenovaná šablona z admin UI
     * 4. page.php
     * 5. index.php (povinný fallback)
     */
    public function resolveTemplate(object $page): string
    {
        $slug     = $page->slug ?? '';
        $id       = $page->id ?? 0;
        $template = $page->template ?? null;

        $candidates = [
            'page-' . $slug . '.php',
            'page-' . $id . '.php',
        ];

        if ($template) {
            $candidates[] = $template;
        }

        $candidates[] = 'page.php';
        $candidates[] = 'index.php';

        foreach ($candidates as $file) {
            // Child theme
            if (file_exists($this->themeDir . '/' . $file)) {
                return $this->themeDir . '/' . $file;
            }
            // Parent theme
            if ($this->parentDir && file_exists($this->parentDir . '/' . $file)) {
                return $this->parentDir . '/' . $file;
            }
        }

        throw new \RuntimeException(
            "Žádná šablona tématu nenalezena. Zkontrolujte, zda existuje {$this->themeDir}/index.php"
        );
    }

    /**
     * Šablona pro homepage (slug "/")
     * 1. home.php
     * 2. page.php
     * 3. index.php
     */
    public function resolveHomeTemplate(): string
    {
        $candidates = ['home.php', 'page.php', 'index.php'];

        foreach ($candidates as $file) {
            if (file_exists($this->themeDir . '/' . $file)) {
                return $this->themeDir . '/' . $file;
            }
            if ($this->parentDir && file_exists($this->parentDir . '/' . $file)) {
                return $this->parentDir . '/' . $file;
            }
        }

        throw new \RuntimeException("Šablona homepage nenalezena.");
    }

    /**
     * Renderuje šablonu s dostupnými proměnnými.
     */
    public function render(string $templateFile, array $vars = []): string
    {
        extract($vars, EXTR_SKIP);

        sp_do_action('render.before', $templateFile);

        ob_start();
        require $templateFile;
        $html = ob_get_clean();

        sp_do_action('render.after', $templateFile);

        return $html;
    }

    /**
     * Vrátí seznam dostupných pojmenovaných šablon (z templates/ adresáře).
     * @return array<string, string> [filepath => name]
     */
    public function getNamedTemplates(): array
    {
        $templates = [];
        $dirs = [$this->themeDir, $this->parentDir];

        foreach (array_filter($dirs) as $dir) {
            $files = glob($dir . '/templates/*.php') ?: [];
            foreach ($files as $file) {
                $relative = 'templates/' . basename($file);
                if (isset($templates[$relative])) {
                    continue;
                }
                $name = $this->extractTemplateName($file);
                if ($name) {
                    $templates[$relative] = $name;
                }
            }
        }

        return $templates;
    }

    public function getThemeSlug(): string
    {
        return $this->themeSlug;
    }

    public function getThemeMeta(?string $slug = null): array
    {
        return $this->loadMeta($slug ?? $this->themeSlug);
    }

    /**
     * Vrátí seznam všech dostupných témat.
     * @return array<string, array>
     */
    public function getAllThemes(): array
    {
        $themes = [];
        $dirs   = glob(BASE_PATH . '/themes/*', GLOB_ONLYDIR) ?: [];

        foreach ($dirs as $dir) {
            $slug = basename($dir);
            $meta = $this->loadMeta($slug);
            if (!empty($meta)) {
                $themes[$slug] = $meta;
            }
        }

        return $themes;
    }

    private function loadMeta(string $slug): array
    {
        $file = BASE_PATH . '/themes/' . $slug . '/theme.php';
        if (!file_exists($file)) {
            return [];
        }
        $meta = require $file;
        return is_array($meta) ? $meta : [];
    }

    private function extractTemplateName(string $file): ?string
    {
        $content = file_get_contents($file, false, null, 0, 512);
        if ($content === false) {
            return null;
        }
        if (preg_match('/Template Name:\s*(.+)/i', $content, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
