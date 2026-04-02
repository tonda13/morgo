<?php

declare(strict_types=1);

// Registrace navigačních menu
sp_add_action('sp_init', function () {
    sp_register_nav_menus([
        'primary' => 'Hlavní navigace',
        'footer'  => 'Patičková navigace',
    ]);
});

// Registrace widget oblastí
sp_add_action('sp_init', function () {
    sp_register_widget_area('sidebar',        'Postranní panel');
    sp_register_widget_area('footer-widgets', 'Widgety v patičce');
});

// CSS a JS
sp_add_action('sp_head', function () {
    sp_enqueue_style('default-theme', theme_url('assets/css/style.css'));
    sp_print_styles();
});

sp_add_action('sp_footer', function () {
    sp_print_scripts(true);
});

// Podpora vlastností jádra
sp_add_theme_support('custom-fields');
sp_add_theme_support('page-thumbnails');
