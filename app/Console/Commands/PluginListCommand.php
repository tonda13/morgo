<?php

declare(strict_types=1);

namespace Morgo\Console\Commands;

use Morgo\Core\PluginLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginListCommand extends Command
{
    protected static $defaultName = 'plugin:list';

    public function __construct(private readonly PluginLoader $pluginLoader)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Zobrazí seznam pluginů.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $plugins = $this->pluginLoader->getAllPlugins();
        $table   = new Table($output);
        $table->setHeaders(['Slug', 'Název', 'Verze', 'Aktivní']);

        foreach ($plugins as $plugin) {
            $table->addRow([
                $plugin['slug'],
                $plugin['name'],
                $plugin['version'],
                $plugin['active'] ? '<info>✓</info>' : '✗',
            ]);
        }

        $table->render();
        return Command::SUCCESS;
    }
}
