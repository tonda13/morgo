<?php

declare(strict_types=1);

namespace Morgo\Tests\Unit\Auth;

use Morgo\Core\Auth\Capabilities;
use Morgo\Core\Auth\RoleManager;
use Morgo\Domain\User\Role;
use Morgo\Domain\User\User;
use PHPUnit\Framework\TestCase;

class RoleManagerTest extends TestCase
{
    private function makeUser(Role $role): User
    {
        return User::fromArray([
            'id'            => 1,
            'email'         => 'test@example.com',
            'password_hash' => 'hash',
            'display_name'  => 'Test User',
            'role'          => $role->value,
            'created_at'    => '2024-01-01 00:00:00',
        ]);
    }

    // --- Admin ---

    public function testAdminHas12Capabilities(): void
    {
        $caps = RoleManager::capabilitiesForRole(Role::Admin->value);
        $this->assertCount(12, $caps);
    }

    public function testAdminCanReadAdmin(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::READ_ADMIN));
    }

    public function testAdminCanEditPages(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::EDIT_PAGES));
    }

    public function testAdminCanDeletePages(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::DELETE_PAGES));
    }

    public function testAdminCanPublishPages(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::PUBLISH_PAGES));
    }

    public function testAdminCanUploadMedia(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::UPLOAD_MEDIA));
    }

    public function testAdminCanDeleteMedia(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::DELETE_MEDIA));
    }

    public function testAdminCanManageMenus(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::MANAGE_MENUS));
    }

    public function testAdminCanManageWidgets(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::MANAGE_WIDGETS));
    }

    public function testAdminCanManageUsers(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::MANAGE_USERS));
    }

    public function testAdminCanManageOptions(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::MANAGE_OPTIONS));
    }

    public function testAdminCanManagePlugins(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::MANAGE_PLUGINS));
    }

    public function testAdminCanManageThemes(): void
    {
        $user = $this->makeUser(Role::Admin);
        $this->assertTrue(RoleManager::can($user, Capabilities::MANAGE_THEMES));
    }

    // --- Editor ---

    public function testEditorCanReadAdmin(): void
    {
        $user = $this->makeUser(Role::Editor);
        $this->assertTrue(RoleManager::can($user, Capabilities::READ_ADMIN));
    }

    public function testEditorCanEditPages(): void
    {
        $user = $this->makeUser(Role::Editor);
        $this->assertTrue(RoleManager::can($user, Capabilities::EDIT_PAGES));
    }

    public function testEditorCanPublishPages(): void
    {
        $user = $this->makeUser(Role::Editor);
        $this->assertTrue(RoleManager::can($user, Capabilities::PUBLISH_PAGES));
    }

    public function testEditorCanUploadMedia(): void
    {
        $user = $this->makeUser(Role::Editor);
        $this->assertTrue(RoleManager::can($user, Capabilities::UPLOAD_MEDIA));
    }

    public function testEditorCannotManagePlugins(): void
    {
        $user = $this->makeUser(Role::Editor);
        $this->assertFalse(RoleManager::can($user, Capabilities::MANAGE_PLUGINS));
    }

    public function testEditorCannotManageUsers(): void
    {
        $user = $this->makeUser(Role::Editor);
        $this->assertFalse(RoleManager::can($user, Capabilities::MANAGE_USERS));
    }

    public function testEditorCannotManageOptions(): void
    {
        $user = $this->makeUser(Role::Editor);
        $this->assertFalse(RoleManager::can($user, Capabilities::MANAGE_OPTIONS));
    }

    public function testEditorCannotManageThemes(): void
    {
        $user = $this->makeUser(Role::Editor);
        $this->assertFalse(RoleManager::can($user, Capabilities::MANAGE_THEMES));
    }

    public function testEditorCannotDeletePages(): void
    {
        $user = $this->makeUser(Role::Editor);
        $this->assertFalse(RoleManager::can($user, Capabilities::DELETE_PAGES));
    }

    public function testEditorCannotDeleteMedia(): void
    {
        $user = $this->makeUser(Role::Editor);
        $this->assertFalse(RoleManager::can($user, Capabilities::DELETE_MEDIA));
    }

    // --- Viewer ---

    public function testViewerCanReadAdmin(): void
    {
        $user = $this->makeUser(Role::Viewer);
        $this->assertTrue(RoleManager::can($user, Capabilities::READ_ADMIN));
    }

    public function testViewerCannotEditPages(): void
    {
        $user = $this->makeUser(Role::Viewer);
        $this->assertFalse(RoleManager::can($user, Capabilities::EDIT_PAGES));
    }

    public function testViewerCannotDeletePages(): void
    {
        $user = $this->makeUser(Role::Viewer);
        $this->assertFalse(RoleManager::can($user, Capabilities::DELETE_PAGES));
    }

    public function testViewerCannotPublishPages(): void
    {
        $user = $this->makeUser(Role::Viewer);
        $this->assertFalse(RoleManager::can($user, Capabilities::PUBLISH_PAGES));
    }

    public function testViewerCannotUploadMedia(): void
    {
        $user = $this->makeUser(Role::Viewer);
        $this->assertFalse(RoleManager::can($user, Capabilities::UPLOAD_MEDIA));
    }

    public function testViewerCannotManagePlugins(): void
    {
        $user = $this->makeUser(Role::Viewer);
        $this->assertFalse(RoleManager::can($user, Capabilities::MANAGE_PLUGINS));
    }

    public function testViewerCannotManageUsers(): void
    {
        $user = $this->makeUser(Role::Viewer);
        $this->assertFalse(RoleManager::can($user, Capabilities::MANAGE_USERS));
    }

    public function testViewerHasOnly1Capability(): void
    {
        $caps = RoleManager::capabilitiesForRole(Role::Viewer->value);
        $this->assertCount(1, $caps);
        $this->assertSame([Capabilities::READ_ADMIN], $caps);
    }

    public function testEditorHas4Capabilities(): void
    {
        $caps = RoleManager::capabilitiesForRole(Role::Editor->value);
        $this->assertCount(4, $caps);
    }

    public function testUnknownRoleReturnsEmptyCapabilities(): void
    {
        $caps = RoleManager::capabilitiesForRole('superadmin');
        $this->assertSame([], $caps);
    }

    public function testUnknownRoleCannotDoAnything(): void
    {
        $user = User::fromArray([
            'id'            => 99,
            'email'         => 'unknown@example.com',
            'password_hash' => 'hash',
            'display_name'  => 'Unknown',
            'role'          => 'superadmin',
            'created_at'    => '2024-01-01 00:00:00',
        ]);

        $this->assertFalse(RoleManager::can($user, Capabilities::READ_ADMIN));
        $this->assertFalse(RoleManager::can($user, Capabilities::EDIT_PAGES));
    }
}
