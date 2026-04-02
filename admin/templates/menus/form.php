<?php
/** @var \Morgo\Domain\Menu\Menu|null $menu */
/** @var \Morgo\Domain\Page\Page[] $allPages */
$isEdit      = $menu !== null;
$pageTitle   = $isEdit ? 'Upravit menu: ' . $menu->name : 'Nové menu';
$breadcrumbs = [['label' => 'Menu', 'url' => admin_url('menus')], ['label' => $isEdit ? $menu->name : 'Nové']];
$adminPrefix = config('app.admin_prefix', 'admin');
$formAction  = $isEdit ? "/admin/menus/{$menu->id}" : "/admin/menus";
$existingItems = $isEdit ? json_encode(array_map(fn($i) => ['label' => $i->label, 'url' => $i->url, 'page_id' => $i->page_id, 'parent_id' => $i->parent_id], $menu->items)) : '[]';
?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900"><?= esc_html($pageTitle) ?></h1>
        <a href="/<?= esc_attr($adminPrefix) ?>/menus" class="btn-secondary">Zpět</a>
    </div>

    <form method="POST" action="<?= esc_url($formAction) ?>" id="menu-form">
        <?= csrf_field() ?>
        <input type="hidden" name="items" id="items-json" value="">

        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Název menu</label>
                    <input type="text" name="name" value="<?= esc_attr($menu->name ?? '') ?>" class="form-input" required>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="text-sm font-medium">Položky menu</h3>
            </div>
            <div class="card-body">
                <div id="menu-items" class="space-y-2 mb-4"></div>

                <div class="grid grid-cols-3 gap-2">
                    <select id="add-page" class="form-select text-sm">
                        <option value="">— přidat stránku —</option>
                        <?php foreach ($allPages as $p): ?>
                            <option value="<?= (int) $p->id ?>" data-label="<?= esc_attr($p->title) ?>" data-url="/<?= esc_attr($p->slug) ?>">
                                <?= esc_html($p->title) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <button type="button" id="add-page-btn" class="btn-secondary text-sm">Přidat stránku</button>
                    <button type="button" id="add-custom-btn" class="btn-secondary text-sm">+ Vlastní odkaz</button>
                </div>
            </div>
        </div>

        <?php
        $navMenus = $GLOBALS['_morgo_nav_menus'] ?? [];
        if ($isEdit && !empty($navMenus)):
        ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="text-sm font-medium">Umístění v tématu</h3>
            </div>
            <div class="card-body space-y-2">
                <?php foreach ($navMenus as $locationSlug => $locationLabel): ?>
                    <?php $assigned = (int) get_option('menu_location_' . $locationSlug, 0); ?>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="locations[]" value="<?= esc_attr($locationSlug) ?>"
                               <?= $assigned === $menu->id ? 'checked' : '' ?> class="rounded">
                        <span class="text-sm text-gray-700"><?= esc_html($locationLabel) ?></span>
                    </label>
                <?php endforeach ?>
            </div>
        </div>
        <?php endif ?>

        <button type="submit" class="btn-primary">Uložit menu</button>
    </form>
</div>

<script>
let items = <?= $existingItems ?>;

function renderItems() {
    const container = document.getElementById('menu-items');
    container.innerHTML = '';
    items.forEach((item, i) => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 p-2 bg-gray-50 rounded border border-gray-200';
        div.innerHTML = `
            <span class="flex-1 text-sm font-medium">${item.label}</span>
            <span class="text-xs text-gray-400">${item.url || ''}</span>
            <button type="button" onclick="removeItem(${i})" class="text-red-500 text-xs hover:text-red-700">&#x2715;</button>`;
        container.appendChild(div);
    });
    document.getElementById('items-json').value = JSON.stringify(items);
}

function removeItem(index) { items.splice(index, 1); renderItems(); }

document.getElementById('add-page-btn').addEventListener('click', () => {
    const sel = document.getElementById('add-page');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;
    items.push({ label: opt.dataset.label, url: opt.dataset.url, page_id: parseInt(opt.value), parent_id: null });
    sel.selectedIndex = 0;
    renderItems();
});

document.getElementById('add-custom-btn').addEventListener('click', () => {
    const label = prompt('Název odkazu:');
    if (!label) return;
    const url = prompt('URL (např. https://... nebo /stranka):');
    if (!url) return;
    items.push({ label, url, page_id: null, parent_id: null });
    renderItems();
});

document.getElementById('menu-form').addEventListener('submit', () => {
    document.getElementById('items-json').value = JSON.stringify(items);
});

renderItems();
</script>
