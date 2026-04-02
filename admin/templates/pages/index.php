<?php
/** @var \Morgo\Domain\Page\Page[] $pages */
/** @var \Morgo\Domain\User\User $user */
use Morgo\Domain\Page\PageStatus;
$pageTitle   = 'Stránky';
$breadcrumbs = [['label' => 'Stránky']];
$adminPrefix = config('app.admin_prefix', 'admin');
?>
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Stránky</h1>
        <a href="/<?= esc_attr($adminPrefix) ?>/pages/new" class="btn-primary">+ Nová stránka</a>
    </div>

    <div class="card">
        <?php if (empty($pages)): ?>
            <div class="card-body">
                <div class="empty-state">
                    <p class="empty-state-title">Žádné stránky</p>
                    <p class="empty-state-description">Začněte vytvořením první stránky.</p>
                    <a href="/<?= esc_attr($adminPrefix) ?>/pages/new" class="btn-primary">Vytvořit stránku</a>
                </div>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Název</th>
                        <th>Slug</th>
                        <th>Stav</th>
                        <th>Upraveno</th>
                        <th>Akce</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($pages as $p): ?>
                        <?php $status = PageStatus::from($p->status) ?>
                        <tr>
                            <td class="font-medium">
                                <a href="/<?= esc_attr($adminPrefix) ?>/pages/<?= (int) $p->id ?>/edit" class="hover:text-primary-600">
                                    <?= esc_html($p->title) ?>
                                </a>
                            </td>
                            <td class="text-gray-500 font-mono text-xs">/<?= esc_html($p->slug) ?></td>
                            <td><span class="badge <?= esc_attr($status->badgeClass()) ?>"><?= esc_html($status->label()) ?></span></td>
                            <td class="text-gray-500 text-xs"><?= esc_html(substr($p->updated_at, 0, 16)) ?></td>
                            <td class="flex items-center gap-2">
                                <a href="/<?= esc_attr($adminPrefix) ?>/pages/<?= (int) $p->id ?>/edit" class="text-sm text-primary-600 hover:text-primary-700">Upravit</a>
                                <?php if ($p->status === 'published'): ?>
                                    <a href="/<?= esc_url($p->slug) ?>" target="_blank" class="text-sm text-gray-500 hover:text-gray-700">Zobrazit ↗</a>
                                <?php endif ?>
                                <button
                                    data-method="delete"
                                    data-url="/<?= esc_attr($adminPrefix) ?>/pages/<?= (int) $p->id ?>/delete"
                                    data-confirm="Opravdu chcete smazat stránku &quot;<?= esc_attr($p->title) ?>&quot;?"
                                    class="text-sm text-red-600 hover:text-red-700">Smazat</button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>
    </div>
</div>
