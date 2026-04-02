<?php
/** @var string $step */
/** @var string|null $error */
/** @var RequirementsChecker $checker */
$steps = [
    'step-1' => 'Požadavky',
    'step-2' => 'Databáze',
    'step-3' => 'Migrace',
    'step-4' => 'Admin účet',
    'step-5' => 'Nastavení',
    'step-6' => 'Hotovo',
];
$currentIndex = array_search($step, array_keys($steps), true);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalace — Morgo CMS</title>
    <link rel="stylesheet" href="<?= esc_url(site_url('admin/dist/admin.css')) ?>">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-2xl mx-auto py-12 px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Morgo CMS</h1>
            <p class="text-gray-500">Instalační průvodce</p>
        </div>

        <!-- Kroky -->
        <div class="flex items-center justify-between mb-8">
            <?php foreach ($steps as $key => $label): ?>
                <?php
                $idx = array_search($key, array_keys($steps), true);
                $cls = $idx < $currentIndex
                    ? 'text-primary-600 font-medium'
                    : ($idx === $currentIndex ? 'text-primary-700 font-bold' : 'text-gray-400');
                ?>
                <div class="flex-1 text-center text-xs <?= $cls ?>">
                    <div class="w-7 h-7 rounded-full mx-auto mb-1 flex items-center justify-center text-sm
                        <?= $idx < $currentIndex ? 'bg-primary-600 text-white' : ($idx === $currentIndex ? 'bg-primary-700 text-white' : 'bg-gray-200 text-gray-500') ?>">
                        <?= $idx < $currentIndex ? '✓' : ($idx + 1) ?>
                    </div>
                    <?= esc_html($label) ?>
                </div>
                <?php if ($idx < count($steps) - 1): ?>
                    <div class="flex-1 h-px bg-gray-200 mx-1"></div>
                <?php endif ?>
            <?php endforeach ?>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="flash flash-error mb-6">
                        <span><?= esc_html($error) ?></span>
                    </div>
                <?php endif ?>

                <?php require $templateFile ?>
            </div>
        </div>
    </div>
</body>
</html>
