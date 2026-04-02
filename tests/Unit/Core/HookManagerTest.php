<?php

declare(strict_types=1);

namespace Morgo\Tests\Unit\Core;

use Morgo\Core\HookManager;
use PHPUnit\Framework\TestCase;

class HookManagerTest extends TestCase
{
    private HookManager $hooks;

    protected function setUp(): void
    {
        $this->hooks = new HookManager();
    }

    public function testAddActionAndDoAction(): void
    {
        $called = false;

        $this->hooks->addAction('test.action', function () use (&$called): void {
            $called = true;
        });

        $this->hooks->doAction('test.action');

        $this->assertTrue($called);
    }

    public function testDoActionPassesArguments(): void
    {
        $received = null;

        $this->hooks->addAction('test.args', function (string $val) use (&$received): void {
            $received = $val;
        });

        $this->hooks->doAction('test.args', 'hello');

        $this->assertSame('hello', $received);
    }

    public function testAddFilterAndApplyFilters(): void
    {
        $this->hooks->addFilter('test.filter', fn(string $v) => $v . '_filtered');

        $result = $this->hooks->applyFilters('test.filter', 'value');

        $this->assertSame('value_filtered', $result);
    }

    public function testApplyFiltersChaining(): void
    {
        $this->hooks->addFilter('chain.filter', fn(string $v) => $v . '_A');
        $this->hooks->addFilter('chain.filter', fn(string $v) => $v . '_B');

        $result = $this->hooks->applyFilters('chain.filter', 'start');

        $this->assertSame('start_A_B', $result);
    }

    public function testActionPriorityOrder(): void
    {
        $order = [];

        $this->hooks->addAction('priority.test', function () use (&$order): void {
            $order[] = 'second';
        }, 20);

        $this->hooks->addAction('priority.test', function () use (&$order): void {
            $order[] = 'first';
        }, 5);

        $this->hooks->addAction('priority.test', function () use (&$order): void {
            $order[] = 'third';
        }, 30);

        $this->hooks->doAction('priority.test');

        $this->assertSame(['first', 'second', 'third'], $order);
    }

    public function testFilterPriorityOrder(): void
    {
        $this->hooks->addFilter('priority.filter', fn(string $v) => $v . '_B', 20);
        $this->hooks->addFilter('priority.filter', fn(string $v) => $v . '_A', 5);

        $result = $this->hooks->applyFilters('priority.filter', 'start');

        $this->assertSame('start_A_B', $result);
    }

    public function testRemoveAction(): void
    {
        $called = false;
        $callback = function () use (&$called): void {
            $called = true;
        };

        $this->hooks->addAction('remove.action', $callback);
        $this->hooks->removeAction('remove.action', $callback);
        $this->hooks->doAction('remove.action');

        $this->assertFalse($called);
    }

    public function testRemoveFilter(): void
    {
        $callback = fn(string $v) => $v . '_should_not_apply';

        $this->hooks->addFilter('remove.filter', $callback);
        $this->hooks->removeFilter('remove.filter', $callback);

        $result = $this->hooks->applyFilters('remove.filter', 'original');

        $this->assertSame('original', $result);
    }

    public function testDoActionWithNoCallbacksDoesNotThrow(): void
    {
        // Nesmí vyhodit výjimku při volání neregistrovaného hooku
        $this->expectNotToPerformAssertions();
        $this->hooks->doAction('nonexistent.hook');
    }

    public function testApplyFiltersWithNoCallbacksReturnsOriginalValue(): void
    {
        $result = $this->hooks->applyFilters('nonexistent.filter', 'original');
        $this->assertSame('original', $result);
    }

    public function testHasAction(): void
    {
        $this->assertFalse($this->hooks->hasAction('my.action'));

        $this->hooks->addAction('my.action', fn() => null);

        $this->assertTrue($this->hooks->hasAction('my.action'));
    }

    public function testHasFilter(): void
    {
        $this->assertFalse($this->hooks->hasFilter('my.filter'));

        $this->hooks->addFilter('my.filter', fn(string $v) => $v);

        $this->assertTrue($this->hooks->hasFilter('my.filter'));
    }

    public function testMultipleActionsOnSameHook(): void
    {
        $count = 0;

        $this->hooks->addAction('multi.action', function () use (&$count): void {
            $count++;
        });
        $this->hooks->addAction('multi.action', function () use (&$count): void {
            $count++;
        });

        $this->hooks->doAction('multi.action');

        $this->assertSame(2, $count);
    }

    public function testRemoveNonexistentActionDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();
        $this->hooks->removeAction('nonexistent', fn() => null);
    }

    public function testRemoveNonexistentFilterDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();
        $this->hooks->removeFilter('nonexistent', fn(string $v) => $v);
    }
}
