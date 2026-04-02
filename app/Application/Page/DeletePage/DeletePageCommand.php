<?php

declare(strict_types=1);

namespace Morgo\Application\Page\DeletePage;

final class DeletePageCommand
{
    public function __construct(public readonly int $id)
    {
    }
}
