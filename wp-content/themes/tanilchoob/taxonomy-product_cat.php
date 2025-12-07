<?php

/**
 * Template for WooCommerce Product Category archives
 *
 * Displays a category hero (title, description, thumbnail) and then the
 * standard WooCommerce product loop with ordering and pagination.
 *
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

get_header('shop');

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10
 * @hooked woocommerce_breadcrumb - 20
 */
do_action('woocommerce_before_main_content');

$term        = get_queried_object();
$term_id     = isset($term->term_id) ? (int) $term->term_id : 0;
$title       = single_term_title('', false);
$description = term_description($term);
$thumb_id    = $term_id ? get_term_meta($term_id, 'thumbnail_id', true) : '';
$thumb_url   = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
?>

<div class="container mt-25 mb-25">
    <div class="category-hero flex gap-20 items-center justify-center">
        <a class="back-btn circle-radius bg-black-03 flex item-center" href="<?php echo esc_url(home_url('/shop/')); ?>">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0.75 7.75H14.75M14.75 7.75L7.75 0.75M14.75 7.75L7.75 14.75" stroke="#909090" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <h1 class="yekan-28 color-black bold"><?php echo esc_html($title); ?></h1>
    </div>
</div>


<div class="category-controls mt-20 mb-20">
        <?php
        /**
         * Show notices, result count, and ordering controls.
         *
         * @hooked woocommerce_output_all_notices - 10
         * @hooked woocommerce_result_count - 20
         * @hooked woocommerce_catalog_ordering - 30
         */
        do_action('woocommerce_before_shop_loop');
        ?>
    </div>
<?php if (woocommerce_product_loop()) : ?>
    <?php woocommerce_product_loop_start(); ?>
    <?php while (have_posts()) : the_post(); ?>
        <?php wc_get_template_part('content', 'product'); ?>
    <?php endwhile; ?>
    <?php woocommerce_product_loop_end(); ?>

    <?php
    /**
     * Pagination.
     *
     * @hooked woocommerce_pagination - 10
     */
    do_action('woocommerce_after_shop_loop');
    ?>
<?php else : ?>
    <?php
    /**
     * No products found.
     *
     * @hooked wc_no_products_found - 10
     */
    do_action('woocommerce_no_products_found');
    ?>
<?php endif; ?>
<?php if (! empty($description)) : ?>
    <div class="yekan-18 color-black-50"><?php echo wp_kses_post($description); ?></div>
<?php endif; ?>
<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10
 */
do_action('woocommerce_after_main_content');

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action('woocommerce_sidebar');

get_footer('shop');
