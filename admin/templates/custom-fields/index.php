<?php
/** @var object $page */
/** @var \Illuminate\Support\Collection $fields */
/** @var \Morgo\Domain\User\User $user */
?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Custom Fields</h1>
        <a href="<?= esc_url(admin_url("pages/{$page->id}/edit")) ?>" class="btn-secondary">
            ← Zpět na stránku
        </a>
    </div>

    <!-- Existující custom fields -->
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="text-sm font-medium text-gray-700">
                Stránka: <span class="font-semibold"><?= esc_html($page->title) ?></span>
            </h3>
        </div>
        <div class="card-body p-0">
            <?php if ($fields->isEmpty()): ?>
                <p class="text-gray-500 text-sm p-4">Žádné custom fields. Přidejte první níže.</p>
            <?php else: ?>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left px-4 py-2 font-medium text-gray-700 w-1/3">Klíč</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-700">Hodnota</th>
                            <th class="px-4 py-2 w-20"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($fields as $field): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-gray-800"><?= esc_html($field->field_key) ?></td>
                                <td class="px-4 py-3 text-gray-600 break-all"><?= esc_html($field->field_value ?? '') ?></td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="<?= esc_url(admin_url("pages/{$page->id}/fields/{$field->id}/delete")) ?>"
                                          onsubmit="return confirm('Opravdu smazat field \'<?= esc_js($field->field_key) ?>\'?')">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium">
                                            Smazat
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            <?php endif ?>
        </div>
    </div>

    <!-- Přidat nový custom field -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-medium text-gray-700">Přidat custom field</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= esc_url(admin_url("pages/{$page->id}/fields")) ?>">
                <?= csrf_field() ?>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="form-label" for="field_key">Klíč</label>
                        <input type="text"
                               id="field_key"
                               name="field_key"
                               class="form-input font-mono"
                               placeholder="napr_klic_fieldu"
                               pattern="[a-zA-Z0-9_-]+"
                               title="Pouze alfanumerické znaky, podtržítko a pomlčka"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Alfanumerické znaky, podtržítko, pomlčka</p>
                    </div>
                    <div>
                        <label class="form-label" for="field_value">Hodnota</label>
                        <input type="text"
                               id="field_value"
                               name="field_value"
                               class="form-input"
                               placeholder="Hodnota fieldu">
                    </div>
                </div>
                <button type="submit" class="btn-primary">
                    Přidat field
                </button>
            </form>
        </div>
    </div>
</div>
