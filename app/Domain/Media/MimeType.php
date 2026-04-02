<?php

declare(strict_types=1);

namespace Morgo\Domain\Media;

use Morgo\Domain\Shared\ValueObject;

final class MimeType extends ValueObject
{
    private const ALLOWED = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
    ];

    public readonly string $value;

    public function __construct(string $mime)
    {
        if (!in_array($mime, self::ALLOWED, true)) {
            throw new \InvalidArgumentException("Nepovoleným MIME typ: {$mime}");
        }
        $this->value = $mime;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->value, 'image/');
    }

    public static function getAllowed(): array
    {
        return self::ALLOWED;
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
