<?php

defined('ABSPATH') || exit;

get_header();

if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}
$page_id = get_queried_object_id();

?>
<div id="page-default" class="page-template-default">
    <div class="container">
        <section class="hero tone-violet flex flex-col gap-10 items-center text-center">
            <h1 class="title bold relative"><?php the_title(); ?></h1>
            <?php $hero_subtitle = get_field('hero_subtitle', $page_id); ?>
            <?php if ($hero_subtitle) : ?>
                <div class="hero_subtitle yekan-12 md:yekan-20"><?php echo wp_kses_post($hero_subtitle); ?></div>
            <?php endif; ?>
        </section>
    </div>
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
