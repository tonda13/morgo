<?php

declare(strict_types=1);

namespace Morgo\Infrastructure\Persistence;

use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Domain\Menu\Menu;
use Morgo\Domain\Menu\MenuItem;
use Morgo\Domain\Menu\MenuRepositoryInterface;

class EloquentMenuRepository implements MenuRepositoryInterface
{
    public function __construct(private readonly Capsule $capsule)
    {
    }

    public function findById(int $id): ?Menu
    {
        $row = $this->capsule->table('menus')->find($id);
        if (!$row) return null;
        $menu = Menu::fromArray((array) $row);
        $menu->items = $this->loadItems($id);
        return $menu;
    }

    public function findBySlug(string $slug): ?Menu
    {
        $row = $this->capsule->table('menus')->where('slug', $slug)->first();
        if (!$row) return null;
        $menu = Menu::fromArray((array) $row);
        $menu->items = $this->loadItems($menu->id);
        return $menu;
    }

    public function findAll(): array
    {
        return $this->capsule->table('menus')
            ->orderBy('name')
            ->get()
            ->map(fn($r) => Menu::fromArray((array) $r))
            ->all();
    }

    public function save(Menu $menu): Menu
    {
        $data = ['name' => $menu->name, 'slug' => $menu->slug, 'updated_at' => date('Y-m-d H:i:s')];
        if (isset($menu->id) && $menu->id > 0) {
            $this->capsule->table('menus')->where('id', $menu->id)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $menu->id = (int) $this->capsule->table('menus')->insertGetId($data);
        }
        return $menu;
    }

    public function delete(int $id): void
    {
        $this->capsule->table('menus')->where('id', $id)->delete();
    }

    public function saveItems(int $menuId, array $items): void
    {
        $this->capsule->table('menu_items')->where('menu_id', $menuId)->delete();
        foreach ($items as $order => $item) {
            $this->capsule->table('menu_items')->insert([
                'menu_id'    => $menuId,
                'label'      => $item['label'],
                'url'        => $item['url'] ?? null,
                'page_id'    => ($item['page_id'] ?? '') ?: null,
                'parent_id'  => ($item['parent_id'] ?? '') ?: null,
                'menu_order' => $order,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function loadItems(int $menuId): array
    {
        return $this->capsule->table('menu_items')
            ->where('menu_id', $menuId)
            ->orderBy('menu_order')
            ->get()
            ->map(fn($r) => MenuItem::fromArray((array) $r))
            ->all();
    }
}
