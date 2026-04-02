<?php

declare(strict_types=1);

namespace Morgo\Domain\Page;

class Page
{
    public int $id;
    public string $slug;
    public string $title;
    public string $status;
    public ?string $template;
    public ?string $content_blocks;
    public ?string $meta_title;
    public ?string $meta_description;
    public ?int $parent_id;
    public int $menu_order;
    public ?int $created_by;
    public string $created_at;
    public string $updated_at;

    public function getStatus(): PageStatus
    {
        return PageStatus::from($this->status);
    }

    public function isPublished(): bool
    {
        return $this->status === PageStatus::Published->value;
    }

    public function publish(): void
    {
        $this->status = PageStatus::Published->value;
    }

    public function unpublish(): void
    {
        $this->status = PageStatus::Draft->value;
    }

    public function getContentBlocks(): array
    {
        if (empty($this->content_blocks)) {
            return [];
        }
        return json_decode($this->content_blocks, true) ?? [];
    }

    public static function fromArray(array $data): self
    {
        $page = new self();
        $page->id               = (int) $data['id'];
        $page->slug             = $data['slug'];
        $page->title            = $data['title'];
        $page->status           = $data['status'] ?? PageStatus::Draft->value;
        $page->template         = $data['template'] ?? null;
        $page->content_blocks   = $data['content_blocks'] ?? null;
        $page->meta_title       = $data['meta_title'] ?? null;
        $page->meta_description = $data['meta_description'] ?? null;
        $page->parent_id        = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
        $page->menu_order       = (int) ($data['menu_order'] ?? 0);
        $page->created_by       = isset($data['created_by']) ? (int) $data['created_by'] : null;
        $page->created_at       = $data['created_at'] ?? date('Y-m-d H:i:s');
        $page->updated_at       = $data['updated_at'] ?? date('Y-m-d H:i:s');
        return $page;
    }
}
