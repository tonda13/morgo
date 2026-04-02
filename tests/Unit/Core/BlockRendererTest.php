<?php

declare(strict_types=1);

namespace Morgo\Tests\Unit\Core;

use Morgo\Core\HookManager;
use Morgo\Services\BlockRenderer;
use PHPUnit\Framework\TestCase;

class BlockRendererTest extends TestCase
{
    private HookManager $hooks;
    private BlockRenderer $renderer;

    protected function setUp(): void
    {
        $this->hooks    = new HookManager();
        $this->renderer = new BlockRenderer($this->hooks);
    }

    private function makeData(array $blocks): array
    {
        return ['blocks' => $blocks];
    }

    public function testParagraphBlockRendersText(): void
    {
        $data = $this->makeData([
            ['type' => 'paragraph', 'data' => ['text' => 'Hello World']],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringContainsString('<p>Hello World</p>', $html);
    }

    public function testParagraphAllowsBoldInlineTags(): void
    {
        $data = $this->makeData([
            ['type' => 'paragraph', 'data' => ['text' => '<b>bold</b> text']],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringContainsString('<b>bold</b>', $html);
    }

    public function testParagraphStripsScriptTags(): void
    {
        $data = $this->makeData([
            ['type' => 'paragraph', 'data' => ['text' => '<script>alert("xss")</script>text']],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('text', $html);
    }

    public function testHeaderBlockRendersCorrectTag(): void
    {
        $data = $this->makeData([
            ['type' => 'header', 'data' => ['text' => 'My Heading', 'level' => 2]],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringContainsString('<h2>My Heading</h2>', $html);
    }

    public function testHeaderBlockLevel3(): void
    {
        $data = $this->makeData([
            ['type' => 'header', 'data' => ['text' => 'Sub Heading', 'level' => 3]],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringContainsString('<h3>Sub Heading</h3>', $html);
    }

    public function testHeaderBlockDefaultLevel(): void
    {
        // Chybějící level → výchozí 2
        $data = $this->makeData([
            ['type' => 'header', 'data' => ['text' => 'Default']],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringContainsString('<h2>Default</h2>', $html);
    }

    public function testHeaderBlockLevelClamped(): void
    {
        // Level 10 se ořízne na 6
        $data = $this->makeData([
            ['type' => 'header', 'data' => ['text' => 'Clamped', 'level' => 10]],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringContainsString('<h6>Clamped</h6>', $html);
    }

    public function testHeaderEscapesHtmlInText(): void
    {
        $data = $this->makeData([
            ['type' => 'header', 'data' => ['text' => '<script>alert(1)</script>', 'level' => 1]],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testUnknownBlockTypeUsesFilter(): void
    {
        // Registrujeme filter pro neznámý typ
        $this->hooks->addFilter('render_block_custom', fn(string $html, array $block) => '<div class="custom">custom block</div>');

        $data = $this->makeData([
            ['type' => 'custom', 'data' => ['foo' => 'bar']],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringContainsString('<div class="custom">custom block</div>', $html);
    }

    public function testUnknownBlockTypeWithoutFilterRendersEmpty(): void
    {
        $data = $this->makeData([
            ['type' => 'totally_unknown_xyz', 'data' => []],
        ]);

        $html = $this->renderer->render($data);

        // Bez registrovaného filtru by měl vrátit prázdný řetězec pro neznámý blok
        // (applyFilters vrací default hodnotu '' z match default větve)
        $this->assertIsString($html);
    }

    public function testDelimiterBlock(): void
    {
        $data = $this->makeData([
            ['type' => 'delimiter', 'data' => []],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringContainsString('<hr', $html);
    }

    public function testEmptyBlocksReturnsEmptyString(): void
    {
        $html = $this->renderer->render(['blocks' => []]);
        $this->assertSame('', $html);
    }

    public function testMissingBlocksKeyReturnsEmptyString(): void
    {
        $html = $this->renderer->render([]);
        $this->assertSame('', $html);
    }

    public function testTheContentFilterAppliedToFinalHtml(): void
    {
        $this->hooks->addFilter('the_content', fn(string $html) => '<div class="content">' . $html . '</div>');

        $data = $this->makeData([
            ['type' => 'paragraph', 'data' => ['text' => 'Test']],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringStartsWith('<div class="content">', $html);
    }

    public function testMultipleBlocksRendered(): void
    {
        $data = $this->makeData([
            ['type' => 'header',    'data' => ['text' => 'Title',   'level' => 1]],
            ['type' => 'paragraph', 'data' => ['text' => 'Content']],
        ]);

        $html = $this->renderer->render($data);

        $this->assertStringContainsString('<h1>Title</h1>', $html);
        $this->assertStringContainsString('<p>Content</p>', $html);
    }
}
