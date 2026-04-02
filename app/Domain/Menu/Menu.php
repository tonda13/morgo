<?php

declare(strict_types=1);

namespace Morgo\Domain\Menu;

class Menu
{
    public int $id;
    public string $name;
    public string $slug;
    /** @var MenuItem[] */
    public array $items = [];
    public string $created_at;
    public string $updated_at;

    public static function fromArray(array $data): self
    {
        $m = new self();
        $m->id         = (int) $data['id'];
        $m->name       = $data['name'];
        $m->slug       = $data['slug'];
        $m->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        $m->updated_at = $data['updated_at'] ?? date('Y-m-d H:i:s');
        return $m;
    }
}
