<?php

declare(strict_types=1);

namespace Morgo\Core\Migration;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Builder as Schema;
use Psr\Log\LoggerInterface;

class CoreMigrationRunner
{
    private Schema $schema;
    private string $migrationsPath;

    public function __construct(
        private readonly Capsule $capsule,
        private readonly LoggerInterface $logger
    ) {
        $this->schema         = $capsule->schema();
        $this->migrationsPath = BASE_PATH . '/database/migrations';
    }

    public function run(): array
    {
        $this->ensureMigrationsTable();

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
            $migration->setSchema($this->schema);

            $this->logger->info("Running migration: {$className}");
            $migration->up();

            $this->capsule->table('migrations')->insert([
                'context'     => 'core',
                'migration'   => $className,
                'batch'       => $batch,
                'executed_at' => date('Y-m-d H:i:s'),
            ]);

            $ran[] = $className;
            $this->logger->info("Migration done: {$className}");
        }

        return $ran;
    }

    public function rollback(int $steps = 1): array
    {
        $this->ensureMigrationsTable();

        $batches = $this->capsule->table('migrations')
            ->where('context', 'core')
            ->orderBy('batch', 'desc')
            ->orderBy('id', 'desc')
            ->limit($steps * 100)
            ->get()
            ->groupBy('batch');

        $rolledBack = [];
        $count = 0;

        foreach ($batches as $batch => $migrations) {
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
                    $migration->setSchema($this->schema);
                    $this->logger->info("Rolling back: {$row->migration}");
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

    public function getStatus(): array
    {
        $this->ensureMigrationsTable();

        $ran     = $this->capsule->table('migrations')->where('context', 'core')->pluck('migration')->toArray();
        $all     = $this->getAllMigrationFiles();
        $status  = [];

        foreach ($all as $file) {
            $class    = $this->getClassName($file);
            $status[] = [
                'migration' => $class,
                'ran'       => in_array($class, $ran, true),
            ];
        }

        return $status;
    }

    private function ensureMigrationsTable(): void
    {
        if (!$this->schema->hasTable('migrations')) {
            $this->schema->create('migrations', function ($table) {
                $table->id();
                $table->string('context')->default('core');
                $table->string('migration');
                $table->integer('batch');
                $table->timestamp('executed_at')->useCurrent();
            });
        }
    }

    private function getPendingMigrations(): array
    {
        $ran = $this->capsule->table('migrations')->where('context', 'core')->pluck('migration')->toArray();
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
        $name = pathinfo($filename, PATHINFO_FILENAME);
        // 2026_01_01_000001_CreatePagesTable → CreatePagesTable
        $parts = explode('_', $name);
        // Přeskočit první 4 části (datum + čas)
        $class = implode('_', array_slice($parts, 4));
        return str_replace('_', '', ucwords($class, '_'));
    }

    private function findFileForClass(string $className): ?string
    {
        $files = $this->getAllMigrationFiles();
        foreach ($files as $file) {
            if ($this->getClassName($file) === $className) {
                return $file;
            }
        }
        return null;
    }

    private function getNextBatch(): int
    {
        $max = $this->capsule->table('migrations')->where('context', 'core')->max('batch');
        return ($max ?? 0) + 1;
    }
}
