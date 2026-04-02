<?php

declare(strict_types=1);

namespace Morgo\Application\Page\DeletePage;

use Morgo\Domain\Page\PageRepositoryInterface;
use Psr\Log\LoggerInterface;

class DeletePageHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(DeletePageCommand $command): void
    {
        $page = $this->pages->findById($command->id);
        if ($page === null) {
            return;
        }

        sp_do_action('page.deleted', $page);
        $this->pages->delete($command->id);
        $this->logger->info('Page deleted', ['id' => $command->id]);
    }
}
