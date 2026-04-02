<?php
/**
 * Template Name: Celá šířka
 */

get_header(); ?>

<main class="container">
    <article class="content full-width">
        <h1 class="page-title"><?php the_title(); ?></h1>
        <div class="page-content">
            <?php the_content(); ?>
        </div>
    </article>
</main>

<?php get_footer(); ?>
