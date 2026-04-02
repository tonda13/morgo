<?php

declare(strict_types=1);

namespace Morgo\Domain\Shared;

abstract class DomainEvent
{
    private readonly \DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
