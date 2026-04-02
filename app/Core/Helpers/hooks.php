<?php

declare(strict_types=1);

use Morgo\Core\HookManager;

/**
 * Globální sp_* wrapper funkce pro HookManager.
 * Nikdy nevolat HookManager přímo z pluginů nebo témat.
 */

function sp_hooks(): HookManager
{
    static $instance = null;
    if ($instance === null) {
        // Pokud běží DI container, vezmi z něj — jinak vytvoř singleton
        global $morgoContainer;
        if ($morgoContainer !== null) {
            $instance = $morgoContainer->get(HookManager::class);
        } else {
            $instance = new HookManager();
        }
    }
    return $instance;
}

function sp_add_action(string $hook, callable $callback, int $priority = 10): void
{
    sp_hooks()->addAction($hook, $callback, $priority);
}

function sp_do_action(string $hook, mixed ...$args): void
{
    sp_hooks()->doAction($hook, ...$args);
}

function sp_add_filter(string $hook, callable $callback, int $priority = 10): void
{
    sp_hooks()->addFilter($hook, $callback, $priority);
}

function sp_apply_filters(string $hook, mixed $value, mixed ...$args): mixed
{
    return sp_hooks()->applyFilters($hook, $value, ...$args);
}

function sp_remove_action(string $hook, callable $callback): void
{
    sp_hooks()->removeAction($hook, $callback);
}

function sp_remove_filter(string $hook, callable $callback): void
{
    sp_hooks()->removeFilter($hook, $callback);
}

function sp_has_action(string $hook): bool
{
    return sp_hooks()->hasAction($hook);
}

function sp_has_filter(string $hook): bool
{
    return sp_hooks()->hasFilter($hook);
}
