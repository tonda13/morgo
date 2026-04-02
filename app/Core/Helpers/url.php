<?php

declare(strict_types=1);

function site_url(string $path = ''): string
{
    $base = rtrim((string) config('app.url', ''), '/');
    $path = $path ? '/' . ltrim($path, '/') : '';
    return $base . $path;
}

function admin_url(string $path = ''): string
{
    $prefix = config('app.admin_prefix', 'admin');
    return site_url($prefix . ($path ? '/' . ltrim($path, '/') : ''));
}

function theme_url(string $path = ''): string
{
    $theme = active_theme_slug();
    $base = site_url('themes/' . $theme);
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function parent_theme_url(string $path = ''): string
{
    $parent = active_parent_theme_slug();
    if ($parent === null) {
        return theme_url($path);
    }
    $base = site_url('themes/' . $parent);
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function plugin_url(string $slug, string $path = ''): string
{
    $base = site_url('plugins/' . $slug);
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function theme_path(string $path = ''): string
{
    $theme = active_theme_slug();
    $base = rtrim(BASE_PATH, '/') . '/themes/' . $theme;
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function plugin_path(string $slug, string $path = ''): string
{
    $base = rtrim(BASE_PATH, '/') . '/plugins/' . $slug;
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function storage_path(string $path = ''): string
{
    $base = rtrim(BASE_PATH, '/') . '/storage';
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function base_path(string $path = ''): string
{
    $base = rtrim(BASE_PATH, '/');
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function active_theme_slug(): string
{
    static $slug = null;
    if ($slug === null) {
        $slug = get_option('active_theme') ?: 'default';
    }
    return $slug;
}

function active_parent_theme_slug(): ?string
{
    static $parent = false;
    if ($parent === false) {
        $themeFile = BASE_PATH . '/themes/' . active_theme_slug() . '/theme.php';
        if (file_exists($themeFile)) {
            $meta = require $themeFile;
            $parent = $meta['parent'] ?? null;
        } else {
            $parent = null;
        }
    }
    return $parent;
}
