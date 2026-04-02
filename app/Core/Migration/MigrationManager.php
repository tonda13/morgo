<?php

declare(strict_types=1);

namespace Morgo\Core\Migration;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Log\LoggerInterface;

class MigrationManager
{
    public function __construct(
        private readonly Capsule $capsule,
        private readonly LoggerInterface $logger
    ) {
    }

    public function runCore(): array
    {
        $runner = new CoreMigrationRunner($this->capsule, $this->logger);
        return $runner->run();
    }

    public function rollbackCore(int $steps = 1): array
    {
        $runner = new CoreMigrationRunner($this->capsule, $this->logger);
        return $runner->rollback($steps);
    }

    public function getStatus(): array
    {
        $runner = new CoreMigrationRunner($this->capsule, $this->logger);
        return $runner->getStatus();
    }

    public function runPlugin(string $slug, string $migrationsPath): array
    {
        $runner = new PluginMigrationRunner($slug, $this->capsule, $this->logger);
        $runner->setMigrationsPath($migrationsPath);
        return $runner->run();
    }

    public function rollbackPlugin(string $slug, string $migrationsPath, int $steps = 1): array
    {
        $runner = new PluginMigrationRunner($slug, $this->capsule, $this->logger);
        $runner->setMigrationsPath($migrationsPath);
        return $runner->rollback($steps);
    }
}
