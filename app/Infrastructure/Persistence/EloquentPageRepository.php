<?php

declare(strict_types=1);

namespace Morgo\Infrastructure\Persistence;

use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Domain\Page\Page;
use Morgo\Domain\Page\PageRepositoryInterface;

class EloquentPageRepository implements PageRepositoryInterface
{
    public function __construct(private readonly Capsule $capsule)
    {
    }

    public function findById(int $id): ?Page
    {
        $row = $this->capsule->table('pages')->find($id);
        return $row ? Page::fromArray((array) $row) : null;
    }

    public function findBySlug(string $slug): ?Page
    {
        $row = $this->capsule->table('pages')->where('slug', $slug)->first();
        return $row ? Page::fromArray((array) $row) : null;
    }

    public function findAll(array $filters = []): array
    {
        $query = $this->capsule->table('pages');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query
            ->orderBy('menu_order')
            ->orderBy('title')
            ->get()
            ->map(fn($row) => Page::fromArray((array) $row))
            ->all();
    }

    public function save(Page $page): Page
    {
        $data = [
            'slug'             => $page->slug,
            'title'            => $page->title,
            'status'           => $page->status,
            'template'         => $page->template,
            'content_blocks'   => $page->content_blocks,
            'meta_title'       => $page->meta_title,
            'meta_description' => $page->meta_description,
            'parent_id'        => $page->parent_id,
            'menu_order'       => $page->menu_order,
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        if (isset($page->id) && $page->id > 0) {
            $this->capsule->table('pages')->where('id', $page->id)->update($data);
        } else {
            $data['created_by'] = $page->created_by;
            $data['created_at'] = date('Y-m-d H:i:s');
            $page->id = (int) $this->capsule->table('pages')->insertGetId($data);
        }

        return $page;
    }

    public function delete(int $id): void
    {
        $this->capsule->table('pages')->where('id', $id)->delete();
    }

    public function exists(string $slug, ?int $excludeId = null): bool
    {
        $query = $this->capsule->table('pages')->where('slug', $slug);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}
