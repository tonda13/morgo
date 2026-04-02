<?php

declare(strict_types=1);

namespace Morgo\Core\Migration;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Builder as Schema;
use Psr\Log\LoggerInterface;

class PluginMigrationRunner
{
    private Schema $schema;
    private string $allowedPrefix;
    private string $migrationsPath;

    public function __construct(
        private readonly string $pluginSlug,
        private readonly Capsule $capsule,
        private readonly LoggerInterface $logger
    ) {
        $this->schema         = $capsule->schema();
        $this->allowedPrefix  = str_replace('-', '_', $pluginSlug) . '_';
        $this->migrationsPath = BASE_PATH . '/plugins/' . $pluginSlug . '/Migrations';
    }

    public function setMigrationsPath(string $path): void
    {
        $this->migrationsPath = $path;
    }

    public function assertTableAllowed(string $tableName): void
    {
        if (!str_starts_with($tableName, $this->allowedPrefix)) {
            throw new MigrationSandboxException(sprintf(
                'Plugin "%s" nemůže operovat s tabulkou "%s". Povoleny jsou pouze tabulky s prefixem "%s".',
                $this->pluginSlug,
                $tableName,
                $this->allowedPrefix
            ));
        }
    }

    public function run(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $pending = $this->getPendingMigrations();
        if (empty($pending)) {
            return [];
        }

        $batch = $this->getNextBatch();
        $ran   = [];

        foreach ($pending as $file) {
            $className = $this->getClassName($file);
            require_once $this->migrationsPath . '/' . $file;

            if (!class_exists($className)) {
                throw new \RuntimeException("Migration class {$className} not found in {$file}");
            }

            /** @var Migration $migration */
            $migration = new $className();
            $migration->setSchema($this->createSandboxedSchema());

            $this->logger->info("Plugin [{$this->pluginSlug}] Running migration: {$className}");
            $migration->up();

            $this->capsule->table('migrations')->insert([
                'context'     => $this->pluginSlug,
                'migration'   => $className,
                'batch'       => $batch,
                'executed_at' => date('Y-m-d H:i:s'),
            ]);

            $ran[] = $className;
        }

        return $ran;
    }

    public function rollback(int $steps = 1): array
    {
        $batches = $this->capsule->table('migrations')
            ->where('context', $this->pluginSlug)
            ->orderBy('batch', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('batch');

        $rolledBack = [];
        $count = 0;

        foreach ($batches as $migrations) {
            if ($count >= $steps) {
                break;
            }

            foreach ($migrations as $row) {
                $file = $this->findFileForClass($row->migration);
                if ($file && file_exists($this->migrationsPath . '/' . $file)) {
                    require_once $this->migrationsPath . '/' . $file;
                }

                if (class_exists($row->migration)) {
                    $migration = new $row->migration();
                    $migration->setSchema($this->createSandboxedSchema());
                    $migration->down();
                }

                $this->capsule->table('migrations')
                    ->where('id', $row->id)
                    ->delete();

                $rolledBack[] = $row->migration;
            }

            $count++;
        }

        return $rolledBack;
    }

    /**
     * Vytvoří proxy Schema Builder, který ověřuje prefix tabulek.
     */
    private function createSandboxedSchema(): Schema
    {
        $runner = $this;
        $schema = $this->schema;

        // Použijeme anonymous class jako proxy
        return new class($schema, $runner) extends Schema {
            public function __construct(
                private readonly Schema $real,
                private readonly PluginMigrationRunner $sandbox
            ) {
                // Nepovoláme parent::__construct — delegujeme na $real
            }

            public function create($table, \Closure $callback): void
            {
                $this->sandbox->assertTableAllowed($table);
                $this->real->create($table, $callback);
            }

            public function table($table, \Closure $callback): void
            {
                $this->sandbox->assertTableAllowed($table);
                $this->real->table($table, $callback);
            }

            public function drop($table): void
            {
                $this->sandbox->assertTableAllowed($table);
                $this->real->drop($table);
            }

            public function dropIfExists($table): void
            {
                $this->sandbox->assertTableAllowed($table);
                $this->real->dropIfExists($table);
            }

            public function rename($from, $to): void
            {
                $this->sandbox->assertTableAllowed($from);
                $this->sandbox->assertTableAllowed($to);
                $this->real->rename($from, $to);
            }

            public function hasTable($table): bool
            {
                return $this->real->hasTable($table);
            }

            public function hasColumn($table, $column): bool
            {
                return $this->real->hasColumn($table, $column);
            }

            public function hasColumns($table, array $columns): bool
            {
                return $this->real->hasColumns($table, $columns);
            }
        };
    }

    private function getPendingMigrations(): array
    {
        $ran = $this->capsule->table('migrations')
            ->where('context', $this->pluginSlug)
            ->pluck('migration')
            ->toArray();

        $all = $this->getAllMigrationFiles();

        return array_filter($all, fn($file) => !in_array($this->getClassName($file), $ran, true));
    }

    private function getAllMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.php') ?: [];
        $files = array_map('basename', $files);
        sort($files);
        return $files;
    }

    private function getClassName(string $filename): string
    {
        $name  = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('_', $name);
        $class = implode('_', array_slice($parts, 4));
        return str_replace('_', '', ucwords($class, '_'));
    }

    private function findFileForClass(string $className): ?string
    {
        foreach ($this->getAllMigrationFiles() as $file) {
            if ($this->getClassName($file) === $className) {
                return $file;
            }
        }
        return null;
    }

    private function getNextBatch(): int
    {
        $max = $this->capsule->table('migrations')
            ->where('context', $this->pluginSlug)
            ->max('batch');
        return ($max ?? 0) + 1;
    }
}
