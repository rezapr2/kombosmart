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

<?php
$parent_term = ($term_id && $term->parent) ? get_term($term->parent, 'product_cat') : null;
get_template_part('template-parts/components/archive-hero', null, [
    'title'      => $title,
    'eyebrow'    => $parent_term ? $parent_term->name : 'دسته‌بندی',
    'count'      => $GLOBALS['wp_query']->found_posts,
    'tone'       => \TanilChoob\Theme\Helper::term_tone($term_id),
    'image_id'   => $thumb_id,
    'back_url'   => $parent_term ? get_term_link($parent_term) : get_permalink(wc_get_page_id('shop')),
    'back_label' => $parent_term ? $parent_term->name : 'همه محصولات',
]);
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
                                        <div class="title yekan-12 md:yekan-20 color-white-80 bold"><?php echo esc_html($sub_name); ?></div>
                                        <div class="more w-fit yekan-13 color-white hidden md:flex items-center transition"> بیشتر
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

<?php get_template_part('template-parts/components/sort-controls'); ?>
<div class="container mt-10">
    <div class="category-products grid grid-cols-2 md:grid-cols-4" id="category-products">
<?php if (woocommerce_product_loop()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <?php get_template_part('template-parts/cards/product-card', null, ['post_id' => get_the_ID()]); ?>
    <?php endwhile; ?>
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
</div>
    <?php
    // Infinite scroll data and loader UI
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
<?php if (! empty($description)) : ?>
    <div class="container mt-30">
        <div class="category-desc yekan-16 md:yekan-18 color-black-60">
            <?php
            // Implement read-more collapse similar to product tabs
            $desc_raw  = (string) $description;
            $desc_text = wp_kses_post($desc_raw); // keep allowed HTML
            $plain     = trim(wp_strip_all_tags($desc_raw));
            $should_collapse = mb_strlen($plain, 'UTF-8') > 250;

            if (! $should_collapse) {
                echo $desc_text;
            } else {
                ?>
                <div class="desc-readmore slide-down-wrapper flex flex-col gap-10">
                    <div class="desc-full yekan-16 md:yekan-18 text-justify color-black-60">
                        <?php echo $desc_text; ?>
                    </div>
                    <div class="desc-toggle desc-toggle-more slide-down-trigger yekan-16 color-primary pointer transition items-center" role="button" aria-expanded="false">نمایش بیشتر متن</div>
                    <div class="desc-toggle desc-toggle-less slide-down-trigger yekan-16 color-primary pointer transition items-center" role="button" aria-expanded="true">نمایش کمتر متن</div>
                </div>
                <?php
            }
            ?>
        </div>
        
    </div>
<?php endif; ?>
<div class="container">
<?php
// FAQs (ACF repeater stored on the term)
$term_faqs = $term_id ? get_field('faqs', get_queried_object()) : array();

if (! empty($term_faqs)) {
    get_template_part('template-parts/components/faqs', null, ['faqs' => $term_faqs]);
}
?>
</div>
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
