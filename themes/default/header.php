<!DOCTYPE html>
<html lang="<?php echo esc_attr(get_option('site_language', 'cs')); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(get_the_title() ? get_the_title() . ' — ' . sp_bloginfo('name') : sp_bloginfo('name')); ?></title>
    <?php sp_head(); ?>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?php echo esc_url(site_url()); ?>" class="site-logo">
                <?php echo esc_html(sp_bloginfo('name')); ?>
            </a>

            <button class="menu-toggle" id="menu-toggle" aria-label="Otevřít menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>

            <nav class="primary-nav" id="primary-nav" role="navigation" aria-label="Hlavní navigace">
                <?php the_menu('primary'); ?>
            </nav>
        </div>
    </header>
