<?php
/** @var \Morgo\Domain\Media\Media[] $items */
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vybrat médium</title>
    <link rel="stylesheet" href="<?= esc_url(site_url('admin/dist/style.css')) ?>">
</head>
<body class="bg-gray-50">
    <div class="p-4">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Vybrat obrázek</h2>

        <?php if (empty($items)): ?>
            <p class="text-gray-500 text-sm">Žádná média k dispozici. Nejprve nahrajte soubory.</p>
        <?php else: ?>
            <div class="grid grid-cols-4 gap-3">
                <?php foreach ($items as $item): ?>
                    <?php if (!$item->isImage()) continue ?>
                    <button
                        type="button"
                        onclick="selectMedia(<?= (int) $item->id ?>, '<?= esc_js($item->getPublicUrl()) ?>', '<?= esc_js($item->alt_text ?? '') ?>', '<?= esc_js($item->caption ?? '') ?>')"
                        class="aspect-square bg-gray-100 rounded overflow-hidden hover:ring-2 hover:ring-primary-500 transition-all">
                        <img src="<?= esc_url($item->getPublicUrl()) ?>" alt="<?= esc_attr($item->alt_text ?? '') ?>"
                             class="w-full h-full object-cover">
                    </button>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>

    <script>
    function selectMedia(id, url, alt, caption) {
        window.parent.postMessage({
            type:    'morgo_media_selected',
            id:      id,
            url:     url,
            alt:     alt,
            caption: caption,
        }, '*');
    }
    </script>
</body>
</html>
