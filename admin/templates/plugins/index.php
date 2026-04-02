<?php
/** @var array $plugins */
$pageTitle   = 'Pluginy';
$breadcrumbs = [['label' => 'Pluginy']];
$adminPrefix = config('app.admin_prefix', 'admin');
?>
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Pluginy</h1>

    <?php if (empty($plugins)): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <p class="empty-state-title">Žádné pluginy</p>
                    <p class="empty-state-description">Přidejte pluginy do composer.json v sekci extra.morgocms-plugins.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($plugins as $plugin): ?>
                <div class="card">
                    <div class="card-body flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900">
                                <?= esc_html($plugin['name']) ?>
                                <span class="text-xs font-normal text-gray-400 ml-2">v<?= esc_html($plugin['version']) ?></span>
                            </h3>
                            <p class="text-xs text-gray-500 font-mono mt-0.5"><?= esc_html($plugin['slug']) ?></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <?php if ($plugin['active']): ?>
                                <span class="badge badge-green">Aktivní</span>
                                <form method="POST" action="/<?= esc_attr($adminPrefix) ?>/plugins/<?= esc_attr($plugin['slug']) ?>/deactivate">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-secondary btn-sm">Deaktivovat</button>
                                </form>
                            <?php else: ?>
                                <span class="badge badge-gray">Neaktivní</span>
                                <form method="POST" action="/<?= esc_attr($adminPrefix) ?>/plugins/<?= esc_attr($plugin['slug']) ?>/activate">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-primary btn-sm">Aktivovat</button>
                                </form>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div>
