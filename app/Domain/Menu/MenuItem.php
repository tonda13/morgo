<?php

declare(strict_types=1);

namespace Morgo\Domain\Menu;

class MenuItem
{
    public int $id;
    public int $menu_id;
    public string $label;
    public ?string $url;
    public ?int $page_id;
    public ?int $parent_id;
    public int $menu_order;
    /** @var MenuItem[] */
    public array $children = [];

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->id         = (int) $data['id'];
        $item->menu_id    = (int) $data['menu_id'];
        $item->label      = $data['label'];
        $item->url        = $data['url'] ?? null;
        $item->page_id    = isset($data['page_id']) ? (int) $data['page_id'] : null;
        $item->parent_id  = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
        $item->menu_order = (int) ($data['menu_order'] ?? 0);
        return $item;
    }
}
