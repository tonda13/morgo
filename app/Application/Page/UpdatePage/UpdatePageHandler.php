<?php

declare(strict_types=1);

namespace Morgo\Application\Page\UpdatePage;

use Morgo\Domain\Page\Page;
use Morgo\Domain\Page\PageRepositoryInterface;
use Psr\Log\LoggerInterface;

class UpdatePageHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(UpdatePageCommand $command): Page
    {
        $page = $this->pages->findById($command->id);
        if ($page === null) {
            throw new \RuntimeException("Stránka #{$command->id} nenalezena.");
        }

        $page->title            = $command->title;
        $page->slug             = $command->slug;
        $page->status           = $command->status;
        $page->content_blocks   = $command->contentBlocks;
        $page->template         = $command->template;
        $page->meta_title       = $command->metaTitle;
        $page->meta_description = $command->metaDescription;
        $page->parent_id        = $command->parentId;
        $page->menu_order       = $command->menuOrder;

        sp_do_action('page.beforeSave', $page);
        $page = $this->pages->save($page);
        sp_do_action('page.saved', $page);

        $this->logger->info('Page updated', ['id' => $page->id, 'slug' => $page->slug]);
        return $page;
    }
}
