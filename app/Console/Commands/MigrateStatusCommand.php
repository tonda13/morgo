<?php

declare(strict_types=1);

namespace Morgo\Console\Commands;

use Morgo\Core\Migration\MigrationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateStatusCommand extends Command
{
    protected static $defaultName = 'migrate:status';

    public function __construct(private readonly MigrationManager $manager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Zobrazí stav všech migrací.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $status = $this->manager->getStatus();
        $table  = new Table($output);
        $table->setHeaders(['Migrace', 'Stav']);

        foreach ($status as $row) {
            $table->addRow([
                $row['migration'],
                $row['ran'] ? '<info>✓ Spuštěna</info>' : '<comment>○ Čeká</comment>',
            ]);
        }

        $table->render();
        return Command::SUCCESS;
    }
}
