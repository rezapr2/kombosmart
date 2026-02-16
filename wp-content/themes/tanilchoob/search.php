<?php

defined('ABSPATH') || exit;

get_header('shop');

do_action('woocommerce_before_main_content');

$search_query = get_search_query();
$title = $search_query ? sprintf('نتایج جستجو برای: %s', $search_query) : 'نتایج جستجو';
?>

<div class="container mt-25 mb-25">
    <div class="category-hero flex gap-20 items-center justify-center">
        <h1 class="yekan-16 md:yekan-28 color-black bold"><?php echo esc_html($title); ?></h1>
    </div>
</div>

<div class="container mt-40">
    
</div>
<div class="container mt-10">
    <div class="category-products grid grid-cols-1 md:grid-cols-4 gap-30" id="category-products">
<?php if (woocommerce_product_loop()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <?php get_template_part('template-parts/cards/product-card', null, ['post_id' => get_the_ID()]); ?>
    <?php endwhile; ?>
<?php else : ?>
    <?php do_action('woocommerce_no_products_found'); ?>
<?php endif; ?>
</div>
    <?php
    global $wp_query;
    $current_paged = max(1, (int) get_query_var('paged'));
    $max_pages     = isset($wp_query->max_num_pages) ? (int) $wp_query->max_num_pages : 1;
    ?>
    <div class="infinite-scroll-data"
         data-current-page="<?php echo esc_attr($current_paged); ?>"
         data-max-pages="<?php echo esc_attr($max_pages); ?>"></div>
    <div class="infinite-scroll-trigger" aria-hidden="true"></div>
    <div class="loadmore-wrapper mt-20">
        <button class="loadmore-btn" type="button" disabled>
            <span class="load-text yekan-14 color-black-60">در حال بارگذاری...</span>
            <span class="spinner" aria-hidden="true"></span>
        </button>
    </div>
</div>
<?php
do_action('woocommerce_after_main_content');
do_action('woocommerce_sidebar');

get_footer('shop');
