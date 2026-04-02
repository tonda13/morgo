<?php
/** @var \Morgo\Domain\Media\Media[] $items */
/** @var int $page, $pages, $total */
/** @var \Morgo\Domain\User\User $user */
$pageTitle   = 'Média';
$breadcrumbs = [['label' => 'Média']];
$adminPrefix = config('app.admin_prefix', 'admin');
?>
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Média <span class="text-gray-400 text-lg font-normal">(<?= (int) $total ?>)</span></h1>
        <label class="btn-primary cursor-pointer">
            + Nahrát soubor
            <input type="file" id="media-upload" accept="image/*,.pdf" class="sr-only" multiple>
        </label>
    </div>

    <!-- Upload progress -->
    <div id="upload-progress" class="hidden mb-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <p class="text-sm text-gray-600" id="upload-status">Nahrávám...</p>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                <div id="upload-bar" class="bg-primary-600 h-1.5 rounded-full transition-all" style="width:0%"></div>
            </div>
        </div>
    </div>

    <?php if (empty($items)): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <p class="empty-state-title">Žádná média</p>
                    <p class="empty-state-description">Nahrajte první soubor kliknutím na tlačítko výše.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($items as $item): ?>
                <div class="card group relative overflow-hidden">
                    <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                        <?php if ($item->isImage()): ?>
                            <img src="<?= esc_url($item->getPublicUrl()) ?>" alt="<?= esc_attr($item->alt_text ?? '') ?>"
                                 class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="text-gray-400 text-center p-2">
                                <div class="text-2xl mb-1">📄</div>
                                <div class="text-xs truncate"><?= esc_html(pathinfo($item->filename, PATHINFO_EXTENSION)) ?></div>
                            </div>
                        <?php endif ?>
                    </div>
                    <div class="p-2">
                        <p class="text-xs text-gray-700 truncate" title="<?= esc_attr($item->filename) ?>"><?= esc_html($item->filename) ?></p>
                        <p class="text-xs text-gray-400"><?= esc_html($item->getFormattedSize()) ?></p>
                    </div>
                    <!-- Akce po najetí -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <button
                            data-method="delete"
                            data-url="/<?= esc_attr($adminPrefix) ?>/media/<?= (int) $item->id ?>/delete"
                            data-confirm="Opravdu smazat tento soubor?"
                            class="bg-red-600 text-white text-xs px-2 py-1 rounded hover:bg-red-700">
                            Smazat
                        </button>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

        <!-- Stránkování -->
        <?php if ($pages > 1): ?>
            <div class="flex justify-center gap-2 mt-6">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'btn-primary' : 'btn-secondary' ?> btn-sm px-3 py-1">
                        <?= $i ?>
                    </a>
                <?php endfor ?>
            </div>
        <?php endif ?>
    <?php endif ?>
</div>

<script>
document.getElementById('media-upload').addEventListener('change', async function() {
    const files    = Array.from(this.files);
    const progress = document.getElementById('upload-progress');
    const status   = document.getElementById('upload-status');
    const bar      = document.getElementById('upload-bar');
    const csrf     = document.querySelector('meta[name="csrf-token"]')?.content || '';

    progress.classList.remove('hidden');
    let done = 0;

    for (const file of files) {
        status.textContent = `Nahrávám ${file.name}...`;
        const fd = new FormData();
        fd.append('file', file);
        fd.append('_csrf_token', csrf);

        await fetch('/<?= esc_js(config('app.admin_prefix', 'admin')) ?>/media', {
            method: 'POST',
            body: fd,
        });

        done++;
        bar.style.width = Math.round((done / files.length) * 100) + '%';
    }

    status.textContent = 'Hotovo!';
    setTimeout(() => location.reload(), 800);
});
</script>
