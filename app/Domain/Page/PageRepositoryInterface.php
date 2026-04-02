<?php

declare(strict_types=1);

namespace Morgo\Domain\Page;

interface PageRepositoryInterface
{
    public function findById(int $id): ?Page;

    public function findBySlug(string $slug): ?Page;

    /** @return Page[] */
    public function findAll(array $filters = []): array;

    public function save(Page $page): Page;

    public function delete(int $id): void;

    public function exists(string $slug, ?int $excludeId = null): bool;
}
