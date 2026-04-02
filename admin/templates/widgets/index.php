<?php
/** @var array $widgetAreas */
/** @var array $widgets */
$pageTitle   = 'Widgety';
$breadcrumbs = [['label' => 'Widgety']];

$widgetsByArea = [];
foreach ($widgets as $w) {
    $widgetsByArea[$w->area_slug][] = $w;
}

$widgetTypes = [
    'text'  => 'Textový widget',
    'html'  => 'HTML widget',
    'menu'  => 'Menu widget',
];
?>
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Widgety</h1>

    <?php if (empty($widgetAreas)): ?>
        <div class="card">
            <div class="card-body">
                <p class="text-gray-500 text-sm">Aktivní téma nemá registrované žádné widget oblasti.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($widgetAreas as $areaSlug => $areaName): ?>
            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="font-semibold text-gray-900"><?= esc_html($areaName) ?></h2>
                    <span class="text-xs font-mono text-gray-400"><?= esc_html($areaSlug) ?></span>
                </div>
                <div class="card-body">
                    <?php $areaWidgets = $widgetsByArea[$areaSlug] ?? [] ?>
                    <?php if (empty($areaWidgets)): ?>
                        <p class="text-sm text-gray-400 mb-4">Žádné widgety v této oblasti.</p>
                    <?php else: ?>
                        <div class="space-y-2 mb-4">
                            <?php foreach ($areaWidgets as $w): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded border border-gray-200">
                                    <div>
                                        <span class="font-medium text-sm"><?= esc_html($w->title ?: $widgetTypes[$w->widget_type] ?? $w->widget_type) ?></span>
                                        <span class="text-xs text-gray-400 ml-2"><?= esc_html($widgetTypes[$w->widget_type] ?? $w->widget_type) ?></span>
                                    </div>
                                    <button data-method="delete" data-url="<?= esc_url(admin_url("widgets/{$w->id}/delete")) ?>" data-confirm="Smazat widget?" class="text-xs text-red-600 hover:text-red-700">Smazat</button>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>

                    <!-- Přidat widget -->
                    <form method="POST" action="<?= esc_url(admin_url('widgets')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="area_slug" value="<?= esc_attr($areaSlug) ?>">
                        <div class="grid grid-cols-3 gap-2">
                            <select name="widget_type" class="form-select text-sm">
                                <?php foreach ($widgetTypes as $type => $label): ?>
                                    <option value="<?= esc_attr($type) ?>"><?= esc_html($label) ?></option>
                                <?php endforeach ?>
                            </select>
                            <input type="text" name="title" placeholder="Název widgetu" class="form-input text-sm">
                            <button type="submit" class="btn-secondary text-sm">+ Přidat</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach ?>
    <?php endif ?>
</div>
