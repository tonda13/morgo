<?php
/** @var array $flashes */
/** @var string $email */
$siteName = get_option('site_name', 'Morgo');
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Přihlášení — <?= esc_html($siteName) ?></title>
    <link rel="stylesheet" href="<?= esc_url(site_url('admin/dist/style.css')) ?>">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><?= esc_html($siteName) ?></h1>
            <p class="text-gray-500 mt-1">Administrace</p>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Přihlásit se</h2>

                <?php foreach ($flashes as $flash): ?>
                    <div class="flash flash-<?= esc_attr($flash['type']) ?> mb-4">
                        <span><?= esc_html($flash['message']) ?></span>
                    </div>
                <?php endforeach ?>

                <form method="POST" action="<?= esc_url(admin_url('login')) ?>" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label for="email" class="form-label">E-mail</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= esc_attr($email ?? '') ?>"
                            class="form-input"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>

                    <div class="mb-6">
                        <label for="password" class="form-label">Heslo</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="remember" value="1" class="rounded border-gray-300">
                            Zapamatovat přihlášení
                        </label>
                    </div>

                    <button type="submit" class="btn-primary w-full justify-center py-2.5">
                        Přihlásit se
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            <?= esc_html($siteName) ?> CMS
        </p>
    </div>
</body>
</html>
