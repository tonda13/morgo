<?php

declare(strict_types=1);

namespace Morgo\Domain\User;

enum Role: string
{
    case Admin  = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Admin  => 'Administrátor',
            self::Editor => 'Editor',
            self::Viewer => 'Čtenář',
        };
    }
}
