<?php

declare(strict_types=1);

namespace Morgo\Core;

abstract class AbstractPlugin implements PluginInterface
{
    public function boot(): void
    {
    }

    public function getMigrationsPath(): ?string
    {
        return null;
    }

    public function activate(): void
    {
    }

    public function deactivate(): void
    {
    }
}
