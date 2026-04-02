<?php

declare(strict_types=1);

namespace Morgo\Domain\Page;

enum PageStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Private   = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Koncept',
            self::Published => 'Publikováno',
            self::Private   => 'Privátní',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft     => 'badge-yellow',
            self::Published => 'badge-green',
            self::Private   => 'badge-gray',
        };
    }
}
