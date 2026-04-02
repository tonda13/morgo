<?php

declare(strict_types=1);

namespace Morgo\Core\Auth;

use Morgo\Domain\User\Role;
use Morgo\Domain\User\User;

class RoleManager
{
    /** @var array<string, string[]> */
    private static array $map = [
        Role::Admin->value => [
            Capabilities::READ_ADMIN,
            Capabilities::EDIT_PAGES,
            Capabilities::DELETE_PAGES,
            Capabilities::PUBLISH_PAGES,
            Capabilities::UPLOAD_MEDIA,
            Capabilities::DELETE_MEDIA,
            Capabilities::MANAGE_MENUS,
            Capabilities::MANAGE_WIDGETS,
            Capabilities::MANAGE_USERS,
            Capabilities::MANAGE_OPTIONS,
            Capabilities::MANAGE_PLUGINS,
            Capabilities::MANAGE_THEMES,
        ],
        Role::Editor->value => [
            Capabilities::READ_ADMIN,
            Capabilities::EDIT_PAGES,
            Capabilities::PUBLISH_PAGES,
            Capabilities::UPLOAD_MEDIA,
        ],
        Role::Viewer->value => [
            Capabilities::READ_ADMIN,
        ],
    ];

    public static function can(User $user, string $capability): bool
    {
        $caps = self::$map[$user->role] ?? [];
        return in_array($capability, $caps, true);
    }

    public static function capabilitiesForRole(string $role): array
    {
        return self::$map[$role] ?? [];
    }
}
