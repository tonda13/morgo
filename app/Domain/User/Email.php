<?php

declare(strict_types=1);

namespace Morgo\Domain\User;

use Morgo\Domain\Shared\ValueObject;

final class Email extends ValueObject
{
    public readonly string $value;

    public function __construct(string $email)
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Neplatná e-mailová adresa: {$email}");
        }

        $this->value = $email;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
