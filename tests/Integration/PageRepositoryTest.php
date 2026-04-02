<?php

declare(strict_types=1);

namespace Morgo\Tests\Integration;

use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Domain\Page\Page;
use Morgo\Domain\Page\PageStatus;
use Morgo\Infrastructure\Persistence\EloquentPageRepository;
use PHPUnit\Framework\TestCase;

class PageRepositoryTest extends TestCase
{
    private Capsule $capsule;
    private EloquentPageRepository $repo;

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

        // Vytvoříme tabulku pages v SQLite in-memory
        $this->capsule->schema()->create('pages', function ($table): void {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('status')->default('draft');
            $table->string('template')->nullable();
            $table->text('content_blocks')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->integer('menu_order')->default(0);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        $this->repo = new EloquentPageRepository($this->capsule);
    }

    protected function tearDown(): void
    {
        $this->capsule->schema()->dropIfExists('pages');
    }

    private function makePage(string $slug = 'test-page', string $title = 'Test Page'): Page
    {
        return Page::fromArray([
            'id'          => 0,
            'slug'        => $slug,
            'title'       => $title,
            'status'      => PageStatus::Draft->value,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function testSaveAndFindById(): void
    {
        $page = $this->makePage();
        $saved = $this->repo->save($page);

        $this->assertGreaterThan(0, $saved->id);

        $found = $this->repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame('test-page', $found->slug);
        $this->assertSame('Test Page', $found->title);
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $result = $this->repo->findById(9999);
        $this->assertNull($result);
    }

    public function testFindBySlug(): void
    {
        $page = $this->makePage('my-slug', 'My Page');
        $this->repo->save($page);

        $found = $this->repo->findBySlug('my-slug');
        $this->assertNotNull($found);
        $this->assertSame('My Page', $found->title);
    }

    public function testFindBySlugReturnsNullWhenNotFound(): void
    {
        $result = $this->repo->findBySlug('nonexistent-slug');
        $this->assertNull($result);
    }

    public function testFindAllReturnsAllPages(): void
    {
        $this->repo->save($this->makePage('page-1', 'Page 1'));
        $this->repo->save($this->makePage('page-2', 'Page 2'));

        $all = $this->repo->findAll();
        $this->assertCount(2, $all);
    }

    public function testFindAllFilterByStatus(): void
    {
        $draft = $this->makePage('draft-page', 'Draft');
        $this->repo->save($draft);

        $published = $this->makePage('published-page', 'Published');
        $published->status = PageStatus::Published->value;
        $this->repo->save($published);

        $publishedPages = $this->repo->findAll(['status' => PageStatus::Published->value]);
        $this->assertCount(1, $publishedPages);
        $this->assertSame('published-page', $publishedPages[0]->slug);
    }

    public function testUpdatePage(): void
    {
        $page = $this->makePage();
        $saved = $this->repo->save($page);

        $saved->title = 'Updated Title';
        $this->repo->save($saved);

        $found = $this->repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame('Updated Title', $found->title);
    }

    public function testDeletePage(): void
    {
        $page = $this->makePage();
        $saved = $this->repo->save($page);

        $this->repo->delete($saved->id);

        $found = $this->repo->findById($saved->id);
        $this->assertNull($found);
    }

    public function testExistsReturnsTrueForExistingSlug(): void
    {
        $this->repo->save($this->makePage('existing-slug'));

        $this->assertTrue($this->repo->exists('existing-slug'));
    }

    public function testExistsReturnsFalseForNonexistentSlug(): void
    {
        $this->assertFalse($this->repo->exists('nonexistent-slug'));
    }

    public function testExistsWithExcludeId(): void
    {
        $page = $this->makePage('my-slug');
        $saved = $this->repo->save($page);

        // Excludujeme ID stránky samotné — nesmí vrátit true
        $this->assertFalse($this->repo->exists('my-slug', $saved->id));
    }

    public function testFindAllFilterBySearch(): void
    {
        $this->repo->save($this->makePage('hello-world', 'Hello World'));
        $this->repo->save($this->makePage('about', 'About Us'));

        $results = $this->repo->findAll(['search' => 'Hello']);
        $this->assertCount(1, $results);
        $this->assertSame('Hello World', $results[0]->title);
    }
}
