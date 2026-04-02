<?php

declare(strict_types=1);

namespace Morgo\Domain\Menu;

interface MenuRepositoryInterface
{
    public function findById(int $id): ?Menu;

    public function findBySlug(string $slug): ?Menu;

    /** @return Menu[] */
    public function findAll(): array;

    public function save(Menu $menu): Menu;

    public function delete(int $id): void;

    public function saveItems(int $menuId, array $items): void;
}
