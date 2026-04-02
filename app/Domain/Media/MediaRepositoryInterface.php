<?php

declare(strict_types=1);

namespace Morgo\Domain\Media;

interface MediaRepositoryInterface
{
    public function findById(int $id): ?Media;

    /** @return Media[] */
    public function findAll(int $limit = 50, int $offset = 0): array;

    public function count(): int;

    public function save(Media $media): Media;

    public function delete(int $id): void;
}
