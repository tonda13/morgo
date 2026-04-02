<?php

declare(strict_types=1);

namespace Morgo\Core;

interface PluginInterface
{
    public static function getSlug(): string;
    public static function getName(): string;
    public static function getVersion(): string;
    public function register(): void;
    public function boot(): void;
    public function getMigrationsPath(): ?string;
    public function activate(): void;
    public function deactivate(): void;
}
