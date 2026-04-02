<?php

declare(strict_types=1);

namespace Morgo\Domain\Page;

class PagePublisher
{
    public function __construct(private readonly PageRepositoryInterface $pages)
    {
    }

    public function publish(Page $page): void
    {
        sp_do_action('page.beforeSave', $page);
        $page->publish();
        $this->pages->save($page);
        sp_do_action('page.saved', $page);
    }

    public function unpublish(Page $page): void
    {
        sp_do_action('page.beforeSave', $page);
        $page->unpublish();
        $this->pages->save($page);
        sp_do_action('page.saved', $page);
    }
}
