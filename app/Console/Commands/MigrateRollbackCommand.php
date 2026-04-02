<?php

declare(strict_types=1);

namespace Morgo\Console\Commands;

use Morgo\Core\Migration\MigrationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateRollbackCommand extends Command
{
    protected static $defaultName = 'migrate:rollback';

    public function __construct(private readonly MigrationManager $manager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Rollback poslední dávky migrací.')
             ->addOption('steps', 's', InputOption::VALUE_OPTIONAL, 'Počet dávek k vrácení', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $steps = (int) $input->getOption('steps');
        $output->writeln("<info>Rollback {$steps} dávek...</info>");
        $rolled = $this->manager->rollbackCore($steps);

        if (empty($rolled)) {
            $output->writeln('<comment>Nic k vrácení.</comment>');
        } else {
            foreach ($rolled as $m) {
                $output->writeln(" ↩ {$m}");
            }
        }

        return Command::SUCCESS;
    }
}
