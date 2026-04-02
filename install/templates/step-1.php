<?php
/** @var \Morgo\Core\Install\RequirementsChecker $checker */
$results = $checker->check();
$allOk   = $checker->allPassed($results);
?>
<h2 class="text-xl font-semibold mb-6">Krok 1 — Kontrola prostředí</h2>

<div class="space-y-2 mb-8">
    <?php foreach ($results as $item): ?>
        <div class="flex items-center justify-between py-2 border-b border-gray-100">
            <span class="text-sm text-gray-700"><?= esc_html($item['name']) ?></span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500"><?= esc_html($item['message']) ?></span>
                <span class="<?= $item['ok'] ? 'text-green-600' : 'text-red-600' ?> font-bold">
                    <?= $item['ok'] ? '✓' : '✗' ?>
                </span>
            </div>
        </div>
    <?php endforeach ?>
</div>

<?php if ($allOk): ?>
    <a href="/install/step-2" class="btn-primary">Pokračovat →</a>
<?php else: ?>
    <p class="text-red-600 text-sm mb-4">Opravte výše uvedené problémy a obnovte stránku.</p>
    <a href="/install/step-1" class="btn-secondary">Zkusit znovu</a>
<?php endif ?>
