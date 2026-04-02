<?php

declare(strict_types=1);

namespace Morgo\Core;

class HookManager
{
    /** @var array<string, array<int, array<callable>>> */
    private array $actions = [];

    /** @var array<string, array<int, array<callable>>> */
    private array $filters = [];

    public function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        $this->actions[$hook][$priority][] = $callback;
    }

    public function doAction(string $hook, mixed ...$args): void
    {
        if (!isset($this->actions[$hook])) {
            return;
        }

        ksort($this->actions[$hook]);

        foreach ($this->actions[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $callback(...$args);
            }
        }
    }

    public function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        $this->filters[$hook][$priority][] = $callback;
    }

    public function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (!isset($this->filters[$hook])) {
            return $value;
        }

        ksort($this->filters[$hook]);

        foreach ($this->filters[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $value = $callback($value, ...$args);
            }
        }

        return $value;
    }

    public function removeAction(string $hook, callable $callback): void
    {
        if (!isset($this->actions[$hook])) {
            return;
        }

        foreach ($this->actions[$hook] as $priority => $callbacks) {
            $this->actions[$hook][$priority] = array_filter(
                $callbacks,
                fn($cb) => $cb !== $callback
            );
        }
    }

    public function removeFilter(string $hook, callable $callback): void
    {
        if (!isset($this->filters[$hook])) {
            return;
        }

        foreach ($this->filters[$hook] as $priority => $callbacks) {
            $this->filters[$hook][$priority] = array_filter(
                $callbacks,
                fn($cb) => $cb !== $callback
            );
        }
    }

    public function hasAction(string $hook): bool
    {
        return !empty($this->actions[$hook]);
    }

    public function hasFilter(string $hook): bool
    {
        return !empty($this->filters[$hook]);
    }
}
