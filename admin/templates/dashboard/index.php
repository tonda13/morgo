<?php
/** @var \Morgo\Domain\User\User $user */
/** @var array $stats */
/** @var array $recentPages */
$pageTitle   = 'Přehled';
$breadcrumbs = [];
?>
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Přehled</h1>
        <a href="/<?= esc_attr(config('app.admin_prefix', 'admin')) ?>/pages/new" class="btn-primary">
            + Nová stránka
        </a>
    </div>

    <!-- Statistiky -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card">
            <div class="card-body">
                <p class="text-sm text-gray-500">Stránky</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?= (int) $stats['pages'] ?></p>
                <a href="/<?= esc_attr(config('app.admin_prefix', 'admin')) ?>/pages" class="text-sm text-primary-600 hover:text-primary-700 mt-2 inline-block">Spravovat →</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-sm text-gray-500">Média</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?= (int) $stats['media'] ?></p>
                <a href="/<?= esc_attr(config('app.admin_prefix', 'admin')) ?>/media" class="text-sm text-primary-600 hover:text-primary-700 mt-2 inline-block">Spravovat →</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-sm text-gray-500">Uživatelé</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?= (int) $stats['users'] ?></p>
                <a href="/<?= esc_attr(config('app.admin_prefix', 'admin')) ?>/users" class="text-sm text-primary-600 hover:text-primary-700 mt-2 inline-block">Spravovat →</a>
            </div>
        </div>
    </div>

    <!-- Poslední stránky -->
    <div class="card">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-900">Naposledy upravené stránky</h2>
            <a href="/<?= esc_attr(config('app.admin_prefix', 'admin')) ?>/pages" class="text-sm text-primary-600 hover:text-primary-700">Zobrazit vše →</a>
        </div>

        <?php if (empty($recentPages)): ?>
            <div class="card-body">
                <div class="empty-state">
                    <p class="empty-state-title">Žádné stránky</p>
                    <p class="empty-state-description">Zatím nemáte žádné stránky. Vytvořte první!</p>
                    <a href="/<?= esc_attr(config('app.admin_prefix', 'admin')) ?>/pages/new" class="btn-primary">Vytvořit stránku</a>
                </div>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Název</th>
                        <th>Stav</th>
                        <th>Upraveno</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recentPages as $page): ?>
                        <tr>
                            <td class="font-medium">
                                <a href="/<?= esc_attr(config('app.admin_prefix', 'admin')) ?>/pages/<?= (int) $page->id ?>/edit" class="hover:text-primary-600">
                                    <?= esc_html($page->title) ?>
                                </a>
                                <span class="text-gray-400 text-xs ml-1">/<?= esc_html($page->slug) ?></span>
                            </td>
                            <td>
                                <?php if ($page->status === 'published'): ?>
                                    <span class="badge badge-green">Publikováno</span>
                                <?php elseif ($page->status === 'draft'): ?>
                                    <span class="badge badge-yellow">Koncept</span>
                                <?php else: ?>
                                    <span class="badge badge-gray">Privátní</span>
                                <?php endif ?>
                            </td>
                            <td class="text-gray-500 text-xs"><?= esc_html($page->updated_at) ?></td>
                            <td>
                                <a href="/<?= esc_attr(config('app.admin_prefix', 'admin')) ?>/pages/<?= (int) $page->id ?>/edit" class="text-sm text-primary-600 hover:text-primary-700">Upravit</a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>
    </div>
</div>
