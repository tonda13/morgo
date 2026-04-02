<?php

declare(strict_types=1);

namespace Morgo\Console\Commands;

use Morgo\Core\PluginLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginEnableCommand extends Command
{
    protected static $defaultName = 'plugin:enable';

    public function __construct(private readonly PluginLoader $pluginLoader)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Aktivuje plugin.')
             ->addArgument('slug', InputArgument::REQUIRED, 'Slug pluginu');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $slug = $input->getArgument('slug');
        $ok   = $this->pluginLoader->activate($slug);
        $output->writeln($ok
            ? "<info>Plugin '{$slug}' aktivován.</info>"
            : "<error>Plugin '{$slug}' nenalezen.</error>"
        );
        return $ok ? Command::SUCCESS : Command::FAILURE;
    }
}
