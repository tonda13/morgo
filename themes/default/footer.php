    <footer class="site-footer">
        <div class="container footer-inner">
            <p class="footer-copy">
                &copy; <?php echo date('Y'); ?> <?php echo esc_html(sp_bloginfo('name')); ?>
            </p>
            <?php the_menu('footer'); ?>
        </div>
    </footer>

    <?php sp_footer(); ?>
</body>
</html>
