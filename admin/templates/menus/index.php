<?php
/** @var \Morgo\Domain\Menu\Menu[] $menus */
$pageTitle   = 'Menu';
$breadcrumbs = [['label' => 'Menu']];
$adminPrefix = config('app.admin_prefix', 'admin');
?>
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Menu</h1>
        <a href="/<?= esc_attr($adminPrefix) ?>/menus/new" class="btn-primary">+ Nové menu</a>
    </div>

    <div class="card">
        <?php if (empty($menus)): ?>
            <div class="card-body">
                <div class="empty-state">
                    <p class="empty-state-title">Žádná menu</p>
                    <p class="empty-state-description">Vytvořte navigační menu pro téma.</p>
                    <a href="/<?= esc_attr($adminPrefix) ?>/menus/new" class="btn-primary">Vytvořit menu</a>
                </div>
            </div>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Název</th><th>Slug</th><th>Položky</th><th>Akce</th></tr></thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($menus as $m): ?>
                        <tr>
                            <td class="font-medium"><a href="/<?= esc_attr($adminPrefix) ?>/menus/<?= (int) $m->id ?>/edit" class="hover:text-primary-600"><?= esc_html($m->name) ?></a></td>
                            <td class="font-mono text-xs text-gray-500"><?= esc_html($m->slug) ?></td>
                            <td class="text-gray-500">—</td>
                            <td class="flex gap-2">
                                <a href="/<?= esc_attr($adminPrefix) ?>/menus/<?= (int) $m->id ?>/edit" class="text-sm text-primary-600">Upravit</a>
                                <button data-method="delete" data-url="/<?= esc_attr($adminPrefix) ?>/menus/<?= (int) $m->id ?>/delete" data-confirm="Smazat toto menu?" class="text-sm text-red-600">Smazat</button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>
    </div>
</div>
