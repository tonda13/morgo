<?php

declare(strict_types=1);

// Tyto funkce předpokládají, že aktuální stránka je dostupná přes globální kontext
// ThemeEngine je nastaví před renderováním šablony

function the_title(): void
{
    echo esc_html(get_the_title());
}

function get_the_title(): string
{
    global $morgoPage;
    if ($morgoPage === null) {
        return '';
    }
    return sp_apply_filters('page.title', $morgoPage->title ?? '');
}

function the_content(): void
{
    echo get_the_content();
}

function get_the_content(): string
{
    global $morgoPage, $morgoContainer;
    if ($morgoPage === null) {
        return '';
    }

    $blocks = $morgoPage->content_blocks ?? null;
    if (empty($blocks)) {
        return '';
    }

    $data = is_string($blocks) ? json_decode($blocks, true) : $blocks;
    if (!is_array($data)) {
        return '';
    }

    /** @var \Morgo\Services\BlockRenderer $renderer */
    $renderer = $morgoContainer->get(\Morgo\Services\BlockRenderer::class);
    return $renderer->render($data);
}

function get_custom_field(string $key): mixed
{
    global $morgoPage;
    if ($morgoPage === null) {
        return null;
    }
    $fields = $morgoPage->custom_fields ?? [];
    return $fields[$key] ?? null;
}

function get_header(): void
{
    load_theme_file('header.php');
}

function get_footer(): void
{
    load_theme_file('footer.php');
}

function get_sidebar(): void
{
    load_theme_file('sidebar.php');
}

function get_template_part(string $slug, ?string $name = null): void
{
    $file = $slug . ($name ? '-' . $name : '') . '.php';
    load_theme_file($file);
}

function load_theme_file(string $filename): void
{
    global $morgoPage;

    $themeDir   = BASE_PATH . '/themes/' . active_theme_slug();
    $parentSlug = active_parent_theme_slug();
    $parentDir  = $parentSlug ? BASE_PATH . '/themes/' . $parentSlug : null;

    // Child theme soubor
    if (file_exists($themeDir . '/' . $filename)) {
        require $themeDir . '/' . $filename;
        return;
    }

    // Parent theme fallback
    if ($parentDir && file_exists($parentDir . '/' . $filename)) {
        require $parentDir . '/' . $filename;
        return;
    }
}

function the_menu(string $location): void
{
    echo get_the_menu($location);
}

function get_the_menu(string $location): string
{
    return sp_apply_filters('render_menu_' . $location, '');
}

function widget_area(string $slug): void
{
    echo sp_apply_filters('render_widget_area_' . $slug, '');
}

function get_option(string $key, mixed $default = null): mixed
{
    global $morgoContainer;
    if ($morgoContainer === null) {
        return $default;
    }
    try {
        $db = $morgoContainer->get(\Illuminate\Database\Capsule\Manager::class);
        $row = $db->table('options')->where('option_key', $key)->first();
        return $row ? $row->option_value : $default;
    } catch (\Throwable) {
        return $default;
    }
}

function update_option(string $key, mixed $value): void
{
    global $morgoContainer;
    if ($morgoContainer === null) {
        return;
    }
    $db = $morgoContainer->get(\Illuminate\Database\Capsule\Manager::class);
    $db->table('options')->updateOrInsert(
        ['option_key' => $key],
        ['option_value' => is_array($value) ? json_encode($value) : (string) $value]
    );
}

function sp_bloginfo(string $key): string
{
    return match ($key) {
        'name'        => (string) get_option('site_name', 'Morgo'),
        'url'         => site_url(),
        'description' => (string) get_option('site_description', ''),
        'admin_email' => (string) get_option('admin_email', ''),
        'language'    => (string) get_option('site_language', 'cs_CZ'),
        default       => '',
    };
}

function sp_head(): void
{
    sp_do_action('sp_head');
}

function sp_footer(): void
{
    sp_do_action('sp_footer');
}

function config(string $key, mixed $default = null): mixed
{
    static $configs = [];

    [$file, $subKey] = array_pad(explode('.', $key, 2), 2, null);

    if (!isset($configs[$file])) {
        $path = BASE_PATH . '/config/' . $file . '.php';
        $configs[$file] = file_exists($path) ? require $path : [];
    }

    if ($subKey === null) {
        return $configs[$file];
    }

    // Podpora tečkové notace pro vnořené klíče
    $parts = explode('.', $subKey);
    $value = $configs[$file];
    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }

    return $value;
}
