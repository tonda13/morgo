<?php
$adminPrefix = config('app.admin_prefix', 'admin');
?>
<div class="text-center py-4">
    <div class="text-green-500 text-6xl mb-4">✓</div>
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Instalace dokončena!</h2>
    <p class="text-gray-500 mb-8">Morgo CMS byl úspěšně nainstalován.</p>

    <div class="flex gap-4 justify-center">
        <a href="/<?= esc_attr($adminPrefix) ?>" class="btn-primary">Přejít do adminu →</a>
        <a href="/" class="btn-secondary">Zobrazit web</a>
    </div>
</div>
