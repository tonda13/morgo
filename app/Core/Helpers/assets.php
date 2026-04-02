<?php

declare(strict_types=1);

/** @var array<string, array{src: string, deps: string[], loaded: bool}> $enqueuedStyles */
$GLOBALS['_morgo_styles']  = [];
/** @var array<string, array{src: string, deps: string[], footer: bool, loaded: bool}> $enqueuedScripts */
$GLOBALS['_morgo_scripts'] = [];

function sp_enqueue_style(string $handle, string $src, array $deps = []): void
{
    $GLOBALS['_morgo_styles'][$handle] = [
        'src'    => $src,
        'deps'   => $deps,
        'loaded' => false,
    ];
}

function sp_enqueue_script(string $handle, string $src, array $deps = [], bool $footer = true): void
{
    $GLOBALS['_morgo_scripts'][$handle] = [
        'src'    => $src,
        'deps'   => $deps,
        'footer' => $footer,
        'loaded' => false,
    ];
}

function sp_print_styles(): void
{
    foreach ($GLOBALS['_morgo_styles'] as $handle => $style) {
        if (!$style['loaded']) {
            echo '<link rel="stylesheet" href="' . esc_url($style['src']) . '">' . PHP_EOL;
            $GLOBALS['_morgo_styles'][$handle]['loaded'] = true;
        }
    }
}

function sp_print_scripts(bool $footer = false): void
{
    foreach ($GLOBALS['_morgo_scripts'] as $handle => $script) {
        if (!$script['loaded'] && $script['footer'] === $footer) {
            echo '<script src="' . esc_url($script['src']) . '"></script>' . PHP_EOL;
            $GLOBALS['_morgo_scripts'][$handle]['loaded'] = true;
        }
    }
}

function sp_add_theme_support(string $feature): void
{
    $GLOBALS['_morgo_theme_support'][$feature] = true;
}

function sp_theme_supports(string $feature): bool
{
    return !empty($GLOBALS['_morgo_theme_support'][$feature]);
}

function sp_register_nav_menus(array $locations): void
{
    foreach ($locations as $location => $description) {
        $GLOBALS['_morgo_nav_menus'][$location] = $description;
    }
}

function sp_register_widget_area(string $slug, string $name): void
{
    $GLOBALS['_morgo_widget_areas'][$slug] = $name;
    sp_do_action('widget.areas', $slug, $name);
}
