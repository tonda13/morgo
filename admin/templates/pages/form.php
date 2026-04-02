<?php
/** @var \Morgo\Domain\Page\Page|null $page */
/** @var \Morgo\Domain\Page\Page[] $allPages */
/** @var \Morgo\Domain\User\User $user */
use Morgo\Domain\Page\PageStatus;

$isEdit      = $page !== null;
$pageTitle   = $isEdit ? 'Upravit: ' . $page->title : 'Nová stránka';
$breadcrumbs = [
    ['label' => 'Stránky', 'url' => admin_url('pages')],
    ['label' => $isEdit ? $page->title : 'Nová stránka'],
];
$adminPrefix = config('app.admin_prefix', 'admin');
$formAction  = $isEdit
    ? "/admin/pages/{$page->id}"
    : "/admin/pages";

$statuses = [
    PageStatus::Draft->value     => PageStatus::Draft->label(),
    PageStatus::Published->value => PageStatus::Published->label(),
    PageStatus::Private->value   => PageStatus::Private->label(),
];
?>
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900"><?= esc_html($pageTitle) ?></h1>
        <div class="flex gap-3">
            <?php if ($isEdit && $page->status === 'published'): ?>
                <a href="/<?= esc_url($page->slug) ?>" target="_blank" class="btn-secondary btn-sm">Zobrazit ↗</a>
            <?php endif ?>
            <a href="/<?= esc_attr($adminPrefix) ?>/pages" class="btn-secondary">Zpět</a>
        </div>
    </div>

    <form method="POST" action="<?= esc_url($formAction) ?>" id="page-form">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="_method" value="POST">
        <?php endif ?>
        <input type="hidden" name="content_blocks" id="content_blocks" value="<?= esc_attr($page->content_blocks ?? '') ?>">
        <script id="editor-initial-data" type="application/json">
        <?= $page ? $page->content_blocks ?? '{}' : '{}' ?>
        </script>
        <?php if ($page): ?>
        <span id="page-id-holder" data-page-id="<?= (int) $page->id ?>" hidden></span>
        <?php endif ?>

        <div class="grid grid-cols-3 gap-6">
            <!-- Hlavní obsah -->
            <div class="col-span-2 space-y-4">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Název stránky</label>
                            <input type="text" name="title" id="page-title"
                                value="<?= esc_attr($page->title ?? '') ?>"
                                class="form-input text-lg font-medium"
                                placeholder="Název stránky"
                                required>
                        </div>
                        <div>
                            <label class="form-label">Slug</label>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400 text-sm">/</span>
                                <input type="text" name="slug" id="page-slug"
                                    value="<?= esc_attr($page->slug ?? '') ?>"
                                    class="form-input font-mono text-sm"
                                    placeholder="slug-stranky">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EditorJS -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-medium text-gray-700">Obsah</h3>
                        <span id="autosave-status" class="text-xs text-gray-400"></span>
                    </div>
                    <div class="card-body p-0">
                        <div id="editorjs" class="min-h-64 px-4 py-3"></div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-medium text-gray-700">SEO</h3>
                    </div>
                    <div class="card-body space-y-3">
                        <div>
                            <label class="form-label">Meta titulek</label>
                            <input type="text" name="meta_title"
                                value="<?= esc_attr($page->meta_title ?? '') ?>"
                                class="form-input"
                                placeholder="Výchozí: název stránky">
                        </div>
                        <div>
                            <label class="form-label">Meta popis</label>
                            <textarea name="meta_description" rows="2" class="form-textarea"
                                placeholder="Krátký popis stránky pro vyhledávače"><?= esc_html($page->meta_description ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Postranní panel -->
            <div class="space-y-4">
                <!-- Publikování -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-medium text-gray-700">Publikování</h3>
                    </div>
                    <div class="card-body space-y-3">
                        <div>
                            <label class="form-label">Stav</label>
                            <select name="status" class="form-select">
                                <?php foreach ($statuses as $val => $label): ?>
                                    <option value="<?= esc_attr($val) ?>" <?= ($page->status ?? 'draft') === $val ? 'selected' : '' ?>>
                                        <?= esc_html($label) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Pořadí v menu</label>
                            <input type="number" name="menu_order"
                                value="<?= (int) ($page->menu_order ?? 0) ?>"
                                class="form-input" min="0">
                        </div>
                        <button type="submit" id="save-btn" class="btn-primary w-full justify-center">
                            <?= $isEdit ? 'Uložit změny' : 'Vytvořit stránku' ?>
                        </button>
                    </div>
                </div>

                <!-- Nadřazená stránka -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-medium text-gray-700">Hierarchie</h3>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Nadřazená stránka</label>
                        <select name="parent_id" class="form-select">
                            <option value="">— žádná —</option>
                            <?php foreach ($allPages as $p): ?>
                                <?php if ($page !== null && $p->id === $page->id) continue ?>
                                <option value="<?= (int) $p->id ?>"
                                    <?= ($page->parent_id ?? null) === $p->id ? 'selected' : '' ?>>
                                    <?= esc_html($p->title) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>

                <!-- Custom Fields (pouze edit mode) -->
                <?php if (isset($page)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-medium text-gray-700">Custom Fields</h3>
                    </div>
                    <div class="card-body">
                        <a href="<?= esc_url(admin_url("pages/{$page->id}/fields")) ?>"
                           class="text-sm text-blue-600 hover:underline">
                            Spravovat custom fields →
                        </a>
                    </div>
                </div>
                <?php endif ?>
            </div>
        </div>
    </form>
</div>

<script>
// Automatické generování slugu z názvu (pouze pro nové stránky)
<?php if (!$isEdit): ?>
const titleInput = document.getElementById('page-title');
const slugInput  = document.getElementById('page-slug');
let slugManual   = false;

slugInput.addEventListener('input', () => { slugManual = true; });
titleInput.addEventListener('input', () => {
    if (!slugManual) {
        slugInput.value = titleInput.value
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s]+/g, '-')
            .replace(/-+/g, '-')
            .trim().replace(/^-|-$/g, '');
    }
});
<?php endif ?>

// Ukládání přes tlačítko — počkáme na EditorJS save
document.getElementById('save-btn').addEventListener('click', async (e) => {
    e.preventDefault();
    if (window.morgoEditor) {
        const data = await window.morgoEditor.save();
        document.getElementById('content_blocks').value = JSON.stringify(data);
    }
    document.getElementById('page-form').submit();
});
</script>
