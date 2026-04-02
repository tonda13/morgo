<?php

declare(strict_types=1);

namespace Morgo\Domain\Shared;

abstract class ValueObject
{
    abstract public function equals(self $other): bool;

    public function __toString(): string
    {
        return '';
    }
}
