<?php

/**
 * Shop page and generic product archives (WooCommerce archive-product.php override).
 *
 * Category and tag archives have their own templates (taxonomy-product_cat.php,
 * taxonomy-product_tag.php); this covers the main shop page with the same look.
 *
 * @package TanilChoob
 */

use TanilChoob\Theme\Helper;

defined('ABSPATH') || exit;

get_header('shop');

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10
 * @hooked woocommerce_breadcrumb - 20
 */
do_action('woocommerce_before_main_content');

global $wp_query;

get_template_part('template-parts/components/archive-hero', null, [
    'title'   => woocommerce_page_title(false),
    'eyebrow' => is_search() ? 'نتایج جستجو' : get_bloginfo('name'),
    'count'   => $wp_query->found_posts,
    'tone'    => 'violet',
]);

$categories = Helper::top_product_categories(8);
?>

<?php if ($categories) : ?>
    <div class="container mt-20">
        <nav class="shop-cats flex items-center" aria-label="دسته‌بندی‌ها">
            <?php foreach ($categories as $category) : ?>
                <a class="shop-cats__chip tone-<?php echo esc_attr(Helper::term_tone($category->term_id)); ?>" href="<?php echo esc_url(get_term_link($category)); ?>">
                    <?php echo esc_html($category->name); ?>
                    <span><?php echo esc_html(number_format_i18n($category->count)); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
<?php endif; ?>

<?php get_template_part('template-parts/components/sort-controls'); ?>

<div class="container mt-10">
    <?php if (woocommerce_product_loop()) : ?>
        <div class="category-products grid grid-cols-2 md:grid-cols-4">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/cards/product-card', null, ['product_id' => get_the_ID()]); ?>
            <?php endwhile; ?>
        </div>
        <div class="sw-pagination">
            <?php woocommerce_pagination(); ?>
        </div>
    <?php else : ?>
        <?php do_action('woocommerce_no_products_found'); ?>
    <?php endif; ?>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10
 */
do_action('woocommerce_after_main_content');

get_footer('shop');
