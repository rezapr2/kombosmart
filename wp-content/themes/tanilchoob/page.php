<?php

defined('ABSPATH') || exit;

get_header();

if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}
$page_id = get_queried_object_id();

?>
<div id="page-default" class="page-template-default">
    <section class="hero container flex flex-col gap-10 items-center">
        <h1 class="title color-primary bold relative"><?php the_title(); ?></h1>
        <div class="hero_subtitle color-black-70 yekan-12 md:yekan-20 text-center"><?php echo get_field('hero_subtitle', $page_id) ?: ''; ?>
    </section>
    <section class="container mt-30">
        <div class="page-content yekan-16 md:yekan-20 color-black-70">
            <?php while (have_posts()) : the_post(); ?>
                <?php the_content(); ?>
            <?php endwhile; ?>
        </div>
    </section>
</div>
<?php
get_footer();
