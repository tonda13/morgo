<?php

declare(strict_types=1);

namespace Morgo\Domain\Page;

use Morgo\Domain\Shared\ValueObject;

final class Slug extends ValueObject
{
    public readonly string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9\/\-]/', '-', $value);
        $value = preg_replace('/-+/', '-', $value);
        $value = trim($value, '-');

        if ($value !== '/' && !preg_match('/^[a-z0-9][a-z0-9\-\/]*$/', $value)) {
            throw new \InvalidArgumentException("Neplatný slug: {$value}");
        }

        $this->value = $value;
    }

    public static function fromTitle(string $title): self
    {
        $slug = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $title);
        if ($slug === false) {
            $slug = strtolower($title);
        }
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', trim($slug));
        return new self($slug ?: 'stranka');
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
