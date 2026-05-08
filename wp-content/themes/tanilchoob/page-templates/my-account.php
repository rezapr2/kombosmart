<?php

/**
 * Template Name: My account
 * Description: A custom my account.
 * @package TanilChoob
 */


defined('ABSPATH') || exit;

get_header();

if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}
$page_id = get_queried_object_id();

?>
<div id="page-default" class="page-template-my-account">
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
