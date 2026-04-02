<?php

declare(strict_types=1);

namespace Morgo\Core\Auth;

class Capabilities
{
    // Stránky
    public const READ_ADMIN   = 'read_admin';
    public const EDIT_PAGES   = 'edit_pages';
    public const DELETE_PAGES = 'delete_pages';
    public const PUBLISH_PAGES = 'publish_pages';

    // Média
    public const UPLOAD_MEDIA = 'upload_media';
    public const DELETE_MEDIA = 'delete_media';

    // Menu a widgety
    public const MANAGE_MENUS    = 'manage_menus';
    public const MANAGE_WIDGETS  = 'manage_widgets';

    // Uživatelé
    public const MANAGE_USERS = 'manage_users';

    // Systém
    public const MANAGE_OPTIONS  = 'manage_options';
    public const MANAGE_PLUGINS  = 'manage_plugins';
    public const MANAGE_THEMES   = 'manage_themes';
}
