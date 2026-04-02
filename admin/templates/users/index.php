<?php
/** @var \Morgo\Domain\User\User[] $users */
$pageTitle   = 'Uživatelé';
$breadcrumbs = [['label' => 'Uživatelé']];
$adminPrefix = config('app.admin_prefix', 'admin');
?>
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Uživatelé</h1>
        <a href="/<?= esc_attr($adminPrefix) ?>/users/new" class="btn-primary">+ Nový uživatel</a>
    </div>
    <div class="card">
        <table class="table">
            <thead><tr><th>Jméno</th><th>E-mail</th><th>Role</th><th>Akce</th></tr></thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="font-medium"><?= esc_html($u->display_name) ?></td>
                        <td class="text-gray-500"><?= esc_html($u->email) ?></td>
                        <td><span class="badge badge-gray"><?= esc_html($u->role) ?></span></td>
                        <td class="flex gap-2">
                            <a href="/<?= esc_attr($adminPrefix) ?>/users/<?= (int) $u->id ?>/edit" class="text-sm text-primary-600">Upravit</a>
                            <button data-method="delete" data-url="/<?= esc_attr($adminPrefix) ?>/users/<?= (int) $u->id ?>/delete" data-confirm="Smazat uživatele?" class="text-sm text-red-600">Smazat</button>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
