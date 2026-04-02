<?php

declare(strict_types=1);

namespace Morgo\Console\Commands;

use Morgo\Core\ThemeEngine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeActivateCommand extends Command
{
    protected static $defaultName = 'theme:activate';

    public function __construct(private readonly ThemeEngine $themeEngine)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Aktivuje téma podle slugu.')
             ->addArgument('slug', InputArgument::REQUIRED, 'Slug tématu');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $slug   = $input->getArgument('slug');
        $themes = $this->themeEngine->getAllThemes();

        if (!isset($themes[$slug])) {
            $output->writeln("<error>Téma '{$slug}' nenalezeno.</error>");
            return Command::FAILURE;
        }

        update_option('active_theme', $slug);
        $output->writeln("<info>Téma '{$themes[$slug]['name']}' aktivováno.</info>");
        return Command::SUCCESS;
    }
}
