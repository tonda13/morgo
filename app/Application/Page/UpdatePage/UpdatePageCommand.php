<?php

declare(strict_types=1);

namespace Morgo\Application\Page\UpdatePage;

final class UpdatePageCommand
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $title,
        public readonly string  $slug,
        public readonly string  $status,
        public readonly ?string $contentBlocks,
        public readonly ?string $template,
        public readonly ?string $metaTitle,
        public readonly ?string $metaDescription,
        public readonly ?int    $parentId,
        public readonly int     $menuOrder,
    ) {
    }
}
