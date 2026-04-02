<?php
/**
 * Admin layout
 *
 * Proměnné dostupné ze scope controlleru přes ob_start/ob_get_clean:
 * @var \Morgo\Domain\User\User $user
 * @var array $flashes
 */

use Morgo\Core\Flash;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$flashes     = Flash::get();
$adminPrefix = config('app.admin_prefix', 'admin');
$siteName    = get_option('site_name', 'Morgo');
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

function admin_nav_active(string $path): string {
    global $currentPath, $adminPrefix;
    return str_contains($currentPath, "/{$adminPrefix}/{$path}") ? 'bg-primary-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
}

// Content šablona se renderuje uvnitř layout.php
// Controllery nastavují $contentTemplate nebo předávají $content jako string
$content = '';
if (isset($contentTemplate) && file_exists($contentTemplate)) {
    ob_start();
    include $contentTemplate;
    $content = ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= esc_attr(csrf_token()) ?>">
    <title><?= esc_html($pageTitle ?? 'Admin') ?> — <?= esc_html($siteName) ?></title>
    <link rel="stylesheet" href="<?= esc_url(site_url('admin/dist/admin.css')) ?>">
</head>
<body class="bg-gray-100 h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside id="admin-sidebar" class="w-64 bg-gray-800 flex-shrink-0 flex flex-col transition-transform duration-200 ease-in-out">

        <!-- Logo -->
        <div class="flex items-center h-16 px-6 bg-gray-900">
            <a href="/<?= esc_attr($adminPrefix) ?>" class="text-white font-bold text-xl">
                <?= esc_html($siteName) ?>
            </a>
        </div>

        <!-- Navigace -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="/<?= esc_attr($adminPrefix) ?>" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= admin_nav_active('') && !str_contains($currentPath, '/pages') && !str_contains($currentPath, '/media') ? 'bg-primary-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                Přehled
            </a>
            <a href="/<?= esc_attr($adminPrefix) ?>/pages" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= admin_nav_active('pages') ?>">
                <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Stránky
            </a>
            <a href="/<?= esc_attr($adminPrefix) ?>/media" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= admin_nav_active('media') ?>">
                <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Média
            </a>
            <a href="/<?= esc_attr($adminPrefix) ?>/menus" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= admin_nav_active('menus') ?>">
                <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                Menu
            </a>
            <a href="/<?= esc_attr($adminPrefix) ?>/widgets" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= admin_nav_active('widgets') ?>">
                <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                Widgety
            </a>

            <div class="pt-4 border-t border-gray-700 mt-4">
                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Systém</p>
                <a href="/<?= esc_attr($adminPrefix) ?>/users" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= admin_nav_active('users') ?>">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                    Uživatelé
                </a>
                <a href="/<?= esc_attr($adminPrefix) ?>/plugins" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= admin_nav_active('plugins') ?>">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Pluginy
                </a>
                <a href="/<?= esc_attr($adminPrefix) ?>/themes" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= admin_nav_active('themes') ?>">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    Témata
                </a>
                <a href="/<?= esc_attr($adminPrefix) ?>/settings" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= admin_nav_active('settings') ?>">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Nastavení
                </a>
            </div>

            <!-- Hook pro pluginy -->
            <?php sp_do_action('admin.menu') ?>
        </nav>

        <!-- Uživatel dole -->
        <div class="flex-shrink-0 px-4 py-3 bg-gray-900 border-t border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center min-w-0">
                    <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white text-sm font-medium flex-shrink-0">
                        <?= esc_html(strtoupper(substr($user->display_name ?? 'A', 0, 1))) ?>
                    </div>
                    <div class="ml-2 min-w-0">
                        <p class="text-sm text-white font-medium truncate"><?= esc_html($user->display_name ?? '') ?></p>
                        <p class="text-xs text-gray-400 truncate"><?= esc_html($user->role ?? '') ?></p>
                    </div>
                </div>
                <form method="POST" action="/<?= esc_attr($adminPrefix) ?>/logout" class="ml-2">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-gray-400 hover:text-white text-xs" title="Odhlásit se">↩</button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Hlavní obsah -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top bar -->
        <header class="bg-white border-b border-gray-200 h-16 flex items-center px-6 flex-shrink-0">
            <!-- Mobile sidebar toggle -->
            <button id="sidebar-toggle" class="md:hidden mr-4 text-gray-500 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <!-- Breadcrumbs -->
            <nav class="flex items-center space-x-2 text-sm text-gray-500">
                <a href="/<?= esc_attr($adminPrefix) ?>" class="hover:text-gray-700">Morgo</a>
                <?php if (!empty($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <span class="text-gray-300">/</span>
                        <?php if (isset($crumb['url'])): ?>
                            <a href="<?= esc_url($crumb['url']) ?>" class="hover:text-gray-700"><?= esc_html($crumb['label']) ?></a>
                        <?php else: ?>
                            <span class="text-gray-900"><?= esc_html($crumb['label']) ?></span>
                        <?php endif ?>
                    <?php endforeach ?>
                <?php endif ?>
            </nav>

            <div class="ml-auto flex items-center gap-3">
                <a href="/" target="_blank" class="text-sm text-gray-500 hover:text-gray-700">
                    Zobrazit web ↗
                </a>
            </div>
        </header>

        <!-- Flash zprávy -->
        <?php if (!empty($flashes)): ?>
            <div class="px-6 pt-4">
                <?php foreach ($flashes as $flash): ?>
                    <div data-flash class="flash flash-<?= esc_attr($flash['type']) ?> mb-2">
                        <span class="flex-1"><?= esc_html($flash['message']) ?></span>
                        <button data-flash-close class="ml-auto text-current opacity-60 hover:opacity-100">✕</button>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <!-- Page obsah -->
        <main class="flex-1 overflow-y-auto p-6">
            <?= $content ?>
        </main>
    </div>

    <script src="<?= esc_url(site_url('admin/dist/admin.js')) ?>"></script>
</body>
</html>
