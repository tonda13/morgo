<?php get_header(); ?>

<div class="container layout-with-sidebar">
    <main class="content">
        <article>
            <h1 class="page-title"><?php the_title(); ?></h1>
            <div class="page-content">
                <?php the_content(); ?>
            </div>
        </article>
    </main>

    <?php if (sp_theme_supports('sidebar') || true): ?>
        <aside class="sidebar">
            <?php get_sidebar(); ?>
        </aside>
    <?php endif ?>
</div>

<?php get_footer(); ?>
