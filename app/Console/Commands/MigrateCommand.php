<?php

declare(strict_types=1);

namespace Morgo\Console\Commands;

use Morgo\Core\Migration\MigrationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';

    public function __construct(private readonly MigrationManager $manager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Spustí čekající migrace databáze.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Spouštím migrace...</info>');
        $ran = $this->manager->runCore();

        if (empty($ran)) {
            $output->writeln('<comment>Žádné nové migrace.</comment>');
        } else {
            foreach ($ran as $migration) {
                $output->writeln(" ✓ {$migration}");
            }
            $output->writeln('<info>Hotovo. Spuštěno: ' . count($ran) . ' migrací.</info>');
        }

        return Command::SUCCESS;
    }
}
