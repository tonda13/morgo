<?php

declare(strict_types=1);

namespace Morgo\Application\Page\CreatePage;

final class CreatePageCommand
{
    public function __construct(
        public readonly string  $title,
        public readonly string  $slug,
        public readonly string  $status,
        public readonly ?string $contentBlocks,
        public readonly ?string $template,
        public readonly ?string $metaTitle,
        public readonly ?string $metaDescription,
        public readonly ?int    $parentId,
        public readonly int     $menuOrder,
        public readonly int     $createdBy,
    ) {
    }
}
