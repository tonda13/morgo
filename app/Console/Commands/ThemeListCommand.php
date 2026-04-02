<?php

declare(strict_types=1);

namespace Morgo\Console\Commands;

use Morgo\Core\ThemeEngine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeListCommand extends Command
{
    protected static $defaultName = 'theme:list';

    public function __construct(private readonly ThemeEngine $themeEngine)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Zobrazí seznam dostupných témat.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $themes = $this->themeEngine->getAllThemes();
        $active = get_option('active_theme', 'default');
        $table  = new Table($output);
        $table->setHeaders(['Slug', 'Název', 'Verze', 'Aktivní']);

        foreach ($themes as $slug => $meta) {
            $table->addRow([
                $slug,
                $meta['name'] ?? '?',
                $meta['version'] ?? '?',
                $slug === $active ? '<info>✓</info>' : '',
            ]);
        }

        $table->render();
        return Command::SUCCESS;
    }
}
