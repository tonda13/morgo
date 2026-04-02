<?php get_header(); ?>

<main class="container">
    <div class="not-found">
        <h1>404</h1>
        <p>Stránka nenalezena.</p>
        <a href="<?php echo esc_url(site_url()); ?>" class="btn">Zpět na hlavní stránku</a>
    </div>
</main>

<?php get_footer(); ?>
