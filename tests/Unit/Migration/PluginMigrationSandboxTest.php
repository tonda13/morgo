<?php

declare(strict_types=1);

namespace Morgo\Tests\Unit\Migration;

use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Core\Migration\MigrationSandboxException;
use Morgo\Core\Migration\PluginMigrationRunner;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class PluginMigrationSandboxTest extends TestCase
{
    private Capsule $capsule;

    protected function setUp(): void
    {
        $this->capsule = new Capsule();
        $this->capsule->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }

    private function makeRunner(string $slug): PluginMigrationRunner
    {
        return new PluginMigrationRunner($slug, $this->capsule, new NullLogger());
    }

    public function testAllowedTablePassesValidation(): void
    {
        $runner = $this->makeRunner('contact-form');

        // contact_form_ je správný prefix
        $this->expectNotToPerformAssertions();
        $runner->assertTableAllowed('contact_form_submissions');
    }

    public function testAllowedTableWithExactPrefixPasses(): void
    {
        $runner = $this->makeRunner('my-plugin');

        $this->expectNotToPerformAssertions();
        $runner->assertTableAllowed('my_plugin_settings');
    }

    public function testCorePagesTableThrowsSandboxException(): void
    {
        $runner = $this->makeRunner('contact-form');

        $this->expectException(MigrationSandboxException::class);
        $runner->assertTableAllowed('pages');
    }

    public function testCoreUsersTableThrowsSandboxException(): void
    {
        $runner = $this->makeRunner('contact-form');

        $this->expectException(MigrationSandboxException::class);
        $runner->assertTableAllowed('users');
    }

    public function testOtherPluginTableThrowsSandboxException(): void
    {
        $runner = $this->makeRunner('contact-form');

        $this->expectException(MigrationSandboxException::class);
        // Tabulka jiného pluginu — wrong prefix
        $runner->assertTableAllowed('other_plugin_data');
    }

    public function testExceptionMessageContainsPluginSlug(): void
    {
        $runner = $this->makeRunner('contact-form');

        try {
            $runner->assertTableAllowed('pages');
            $this->fail('Expected MigrationSandboxException was not thrown');
        } catch (MigrationSandboxException $e) {
            $this->assertStringContainsString('contact-form', $e->getMessage());
            $this->assertStringContainsString('pages', $e->getMessage());
            $this->assertStringContainsString('contact_form_', $e->getMessage());
        }
    }

    public function testDashesInSlugConvertedToUnderscoresForPrefix(): void
    {
        // slug "my-cool-plugin" → prefix "my_cool_plugin_"
        $runner = $this->makeRunner('my-cool-plugin');

        $this->expectNotToPerformAssertions();
        $runner->assertTableAllowed('my_cool_plugin_data');
    }

    public function testDashesInSlugDoNotAllowDashPrefixTable(): void
    {
        // Tabulka s pomlčkami v názvu nesmí projít, pokud prefix je s podtržítky
        $runner = $this->makeRunner('my-cool-plugin');

        $this->expectException(MigrationSandboxException::class);
        $runner->assertTableAllowed('my-cool-plugin_data');
    }

    public function testSimplePluginSlugWithoutDashes(): void
    {
        $runner = $this->makeRunner('gallery');

        $this->expectNotToPerformAssertions();
        $runner->assertTableAllowed('gallery_images');
    }

    public function testSimplePluginSlugDoesNotAllowMigrationsTable(): void
    {
        $runner = $this->makeRunner('gallery');

        $this->expectException(MigrationSandboxException::class);
        $runner->assertTableAllowed('migrations');
    }
}
