<?php
/** @var array $options */
$pageTitle   = 'Nastavení';
$breadcrumbs = [['label' => 'Nastavení']];
?>
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Nastavení webu</h1>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= esc_url(admin_url('settings')) ?>">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="form-label">Název webu</label>
                    <input type="text" name="site_name" value="<?= esc_attr($options['site_name'] ?? '') ?>" class="form-input" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Popis webu</label>
                    <input type="text" name="site_description" value="<?= esc_attr($options['site_description'] ?? '') ?>" class="form-input">
                </div>
                <div class="mb-4">
                    <label class="form-label">E-mail administrátora</label>
                    <input type="email" name="admin_email" value="<?= esc_attr($options['admin_email'] ?? '') ?>" class="form-input">
                </div>
                <div class="mb-6">
                    <label class="form-label">Jazyk webu</label>
                    <select name="site_language" class="form-select">
                        <option value="cs_CZ" <?= ($options['site_language'] ?? '') === 'cs_CZ' ? 'selected' : '' ?>>Čeština (cs_CZ)</option>
                        <option value="en_US" <?= ($options['site_language'] ?? '') === 'en_US' ? 'selected' : '' ?>>English (en_US)</option>
                        <option value="sk_SK" <?= ($options['site_language'] ?? '') === 'sk_SK' ? 'selected' : '' ?>>Slovenčina (sk_SK)</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Uložit nastavení</button>
            </form>
        </div>
    </div>
</div>
