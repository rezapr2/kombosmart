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
                <path d="M0.75 7.75H14.75M14.75 7.75L7.75 0.75M14.75 7.75L7.75 14.75" stroke="#909090" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>
        <h1 class="yekan-28 color-black bold"><?php echo esc_html($title); ?></h1>
    </div>
</div>


<?php
// If this category has child categories, show a slider of subcategories
$subcategories = array();
if ($term_id) {
    $subcategories = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => $term_id,
        'hide_empty' => false,
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
    ]);
}
?>
<?php if (!is_wp_error($subcategories) && !empty($subcategories)) : ?>
    <div class="container-right mb-25">
        <div class="carousel_slider-wrapper subcategories-slider" data-slidesPerView="auto" data-spaceBetween="8">
            <div class="swiper carousel_slider">
                <div class="swiper-wrapper">
                    <?php foreach ($subcategories as $subcategory) :
                        $sub_id    = (int) $subcategory->term_id;
                        $sub_name  = $subcategory->name;
                        $sub_link  = get_term_link($subcategory);
                        $sub_thumb_id  = get_term_meta($sub_id, 'thumbnail_id', true);
                        $sub_thumb_url = $sub_thumb_id ? wp_get_attachment_image_url($sub_thumb_id, 'large') : wc_placeholder_img_src('large');
                    ?>
                        <div class="swiper-slide">
                            <a href="<?php echo esc_url($sub_link); ?>" class="block category-item">
                                <div class="relative overflow-hidden">
                                    <img src="<?php echo esc_url($sub_thumb_url); ?>" alt="<?php echo esc_attr($sub_name); ?>">
                                    <div class="content">
                                        <div class="title yekan-20 color-white-80 bold"><?php echo esc_html($sub_name); ?></div>
                                        <div class="more w-fit yekan-13 color-white flex items-center transition"> بیشتر
                                            <div class="arrow flex">
                                                <svg viewBox="0 0 9 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M8 4H2m2-3L1 4l3 3" stroke="#000" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="container mt-40">
    <div class="category-controls py-25">
        <?php
        // Show notices and result count (without default dropdown ordering)
        if (function_exists('woocommerce_output_all_notices')) {
            woocommerce_output_all_notices();
        }

        // Current orderby from query (fallback to menu_order)
        $current_orderby = isset($_GET['orderby']) ? wc_clean(wp_unslash($_GET['orderby'])) : 'menu_order';

        // Define our custom sort options to match the desired UI
        $sort_options = array(
            'date'       => 'جدیدترین',      // Newest
            'price-desc' => 'گران‌ترین',      // Most expensive
            'price'      => 'ارزان‌ترین',     // Cheapest
            'popularity' => 'پربازدیدترین',   // Most viewed/popular
            // You can add 'rating' => 'بالاترین امتیاز' if needed
        );
        ?>
        <div class="flex items-center gap-15">
            <div class="flex items-center color-black-30">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 7H21" stroke="black" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M6 12H18" stroke="black" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M10 17H14" stroke="black" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <span class="yekan-14 color-black-60">مرتب سازی بر اساس:</span>
            <div class="flex items-center gap-20">
                <?php foreach ($sort_options as $orderby => $label):
                    // Build link preserving existing query args while setting orderby
                    $url = add_query_arg(array('orderby' => $orderby));
                    $is_active = ($current_orderby === $orderby);
                ?>
                    <a href="<?php echo esc_url($url); ?>" class="yekan-14 pointer <?php echo $is_active ? 'color-primary' : 'color-black-30'; ?>">
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="flex-grow"></div>
            
        </div>
    </div>
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
