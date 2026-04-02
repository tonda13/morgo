<?php
/** @var array $themes, $active */
$pageTitle   = 'Témata';
$breadcrumbs = [['label' => 'Témata']];
$adminPrefix = config('app.admin_prefix', 'admin');
?>
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Témata</h1>
    <div class="grid grid-cols-2 gap-6">
        <?php foreach ($themes as $slug => $meta): ?>
            <div class="card <?= $slug === $active ? 'ring-2 ring-primary-500' : '' ?>">
                <div class="aspect-video bg-gray-100 rounded-t-lg flex items-center justify-center">
                    <?php $screenshot = BASE_PATH . "/themes/{$slug}/screenshot.png" ?>
                    <?php if (file_exists($screenshot)): ?>
                        <img src="<?= esc_url(site_url("themes/{$slug}/screenshot.png")) ?>" alt="" class="w-full h-full object-cover rounded-t-lg">
                    <?php else: ?>
                        <span class="text-gray-400 text-sm">Náhled nedostupný</span>
                    <?php endif ?>
                </div>
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold"><?= esc_html($meta['name']) ?></h3>
                            <p class="text-xs text-gray-500">v<?= esc_html($meta['version'] ?? '?') ?> · <?= esc_html($meta['author'] ?? '') ?></p>
                        </div>
                        <?php if ($slug === $active): ?>
                            <span class="badge badge-green">Aktivní</span>
                        <?php else: ?>
                            <form method="POST" action="/<?= esc_attr($adminPrefix) ?>/themes/<?= esc_attr($slug) ?>/activate">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-primary btn-sm">Aktivovat</button>
                            </form>
                        <?php endif ?>
                    </div>
                    <?php if (!empty($meta['description'])): ?>
                        <p class="text-xs text-gray-500 mt-2"><?= esc_html($meta['description']) ?></p>
                    <?php endif ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>
