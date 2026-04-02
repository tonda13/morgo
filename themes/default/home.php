<?php get_header(); ?>

<main class="container">
    <?php if (get_the_title()): ?>
        <h1 class="page-title"><?php the_title(); ?></h1>
    <?php endif ?>

    <div class="page-content">
        <?php the_content(); ?>
    </div>
</main>

<?php get_footer(); ?>
