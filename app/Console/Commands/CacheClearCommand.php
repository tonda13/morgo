<?php

declare(strict_types=1);

namespace Morgo\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClearCommand extends Command
{
    protected static $defaultName = 'cache:clear';

    protected function configure(): void
    {
        $this->setDescription('Vymaže DI cache a jiné dočasné soubory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheDir = BASE_PATH . '/storage/cache';
        $cleared  = 0;

        if (is_dir($cacheDir)) {
            foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cacheDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            ) as $file) {
                if ($file->isFile()) {
                    unlink($file->getPathname());
                    $cleared++;
                }
            }
        }

        $output->writeln("<info>Cache vymazána. Smazáno souborů: {$cleared}</info>");
        return Command::SUCCESS;
    }
}
