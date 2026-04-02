<?php
/** @var \Morgo\Domain\User\User|null $editUser */
use Morgo\Domain\User\Role;
$isEdit      = $editUser !== null;
$pageTitle   = $isEdit ? 'Upravit: ' . $editUser->display_name : 'Nový uživatel';
$breadcrumbs = [['label' => 'Uživatelé', 'url' => admin_url('users')], ['label' => $isEdit ? $editUser->display_name : 'Nový']];
$formAction  = $isEdit ? admin_url("users/{$editUser->id}") : admin_url('users');
?>
<div class="max-w-lg mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900"><?= esc_html($pageTitle) ?></h1>
        <a href="<?= esc_url(admin_url('users')) ?>" class="btn-secondary">Zpět</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= esc_url($formAction) ?>">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="form-label">Jméno</label>
                    <input type="text" name="display_name" value="<?= esc_attr($editUser->display_name ?? '') ?>" class="form-input" required>
                </div>
                <?php if (!$isEdit): ?>
                    <div class="mb-4">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-input" required>
                    </div>
                <?php endif ?>
                <div class="mb-4">
                    <label class="form-label"><?= $isEdit ? 'Nové heslo (ponechat prázdné = nezměnit)' : 'Heslo' ?></label>
                    <input type="password" name="password" class="form-input" <?= $isEdit ? '' : 'required' ?> minlength="8">
                </div>
                <div class="mb-6">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <?php foreach (Role::cases() as $role): ?>
                            <option value="<?= esc_attr($role->value) ?>" <?= ($editUser->role ?? '') === $role->value ? 'selected' : '' ?>>
                                <?= esc_html($role->label()) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary"><?= $isEdit ? 'Uložit změny' : 'Vytvořit uživatele' ?></button>
            </form>
        </div>
    </div>
</div>
