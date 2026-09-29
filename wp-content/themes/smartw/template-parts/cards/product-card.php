<?php

/**
 * Product Card Template for WooCommerce
 *
 * Used in product grids (homepage, category/tag archives, search) and Swiper sliders,
 * so the root keeps the `swiper-slide` class.
 *
 * @package TanilChoob
 */

use TanilChoob\Theme\Helper;

$product_id = isset($args['product_id']) ? $args['product_id'] : (isset($args['post_id']) ? $args['post_id'] : get_the_ID());
$product    = wc_get_product($product_id);

if (!$product) {
    return;
}

$title     = $product->get_name();
$permalink = $product->get_permalink();
$image_id  = $product->get_image_id();
$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail') : wc_placeholder_img_src('woocommerce_thumbnail');
$excerpt   = wp_strip_all_tags($product->get_short_description());

$term      = Helper::product_primary_term($product_id);
$tone      = $term ? Helper::term_tone($term->term_id) : 'violet';

$regular_price  = $product->get_regular_price();
$sale_price     = $product->get_sale_price();
$price_html     = $product->get_price_html();
$average_rating = (float) $product->get_average_rating();
$on_sale        = $product->is_on_sale();

// If product is variable, show default variation price instead of price range
if ($product->is_type('variable')) {
    $attributes = $product->get_default_attributes();
    if (!empty($attributes)) {
        foreach ($attributes as $key => $value) {
            $attributes['attribute_' . $key] = $value;
            unset($attributes[$key]);
        }
        $data_store           = \WC_Data_Store::load('product');
        $default_variation_id = $data_store->find_matching_product_variation($product, $attributes);
        if ($default_variation_id) {
            $default_variation = wc_get_product($default_variation_id);
            if ($default_variation && $default_variation->get_price_html()) {
                $price_html = $default_variation->get_price_html();
            }
        }
    }
}

if ($price_html) {
    $price_html = preg_replace('/<\/?(ins)[^>]*>/', '', $price_html);
}

$discount_percentage = 0;
if ($on_sale && $regular_price > 0 && $sale_price !== '') {
    $discount_percentage = round(($regular_price - $sale_price) / $regular_price * 100);
}

// Availability follows WooCommerce stock.
$is_available = $product->is_in_stock();
$status_label = __('ناموجود', 'tanilchoob');

// In stock but no price set yet: show "call for price" instead of a price.
$price_on_request = $is_available && $product->get_price() === '';

$contact_mode = Helper::get_options_field('contact_mode');
$can_quick_buy = !$contact_mode && $is_available && $product->is_type('simple') && $product->is_purchasable();
?>

<div class="product-card swiper-slide tone-<?php echo esc_attr($tone); ?><?php echo $on_sale ? ' is-on-sale' : ''; ?><?php echo $is_available ? '' : ' is-unavailable'; ?>">
    <div class="product-card__inner flex flex-col h-100">
        <a class="product-card__media relative overflow-hidden" href="<?php echo esc_url($permalink); ?>" tabindex="-1" aria-hidden="true">
            <?php if ($discount_percentage > 0) : ?>
                <span class="product-card__discount"><?php echo esc_html($discount_percentage . '٪'); ?></span>
            <?php endif; ?>
            <?php $condition_badge = Helper::product_condition_badge($product_id); ?>
            <?php if ($condition_badge) : ?>
                <span class="product-card__condition tone-<?php echo esc_attr($condition_badge['tone']); ?>"><?php echo esc_html($condition_badge['label']); ?></span>
            <?php endif; ?>
            <img class="product-card__img" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
        </a>

        <div class="product-card__content flex flex-col">
            <div class="product-card__meta flex items-center justify-between">
                <div class="product-card__price">
                    <?php if ($price_on_request) : ?>
                        <span class="product-card__status is-on-request">استعلام قیمت</span>
                    <?php elseif ($is_available && $price_html) : ?>
                        <?php echo $price_html; ?>
                    <?php else : ?>
                        <span class="product-card__status"><?php echo esc_html($status_label); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($term) : ?>
                    <span class="sw-badge"><?php echo esc_html($term->name); ?></span>
                <?php endif; ?>
            </div>

            <h3 class="product-card__title">
                <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
            </h3>

            <?php if ($excerpt) : ?>
                <p class="product-card__excerpt"><?php echo esc_html($excerpt); ?></p>
            <?php endif; ?>

            <?php if ($average_rating > 0) : ?>
                <div class="product-card__rating flex items-center" aria-label="<?php echo esc_attr(sprintf('امتیاز %s از ۵', $average_rating)); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 2.9 6.26 6.87.72-5.13 4.62 1.44 6.76L12 16.9l-6.08 3.46 1.44-6.76L2.23 8.98l6.87-.72L12 2Z" fill="currentColor"/></svg>
                    <span><?php echo esc_html(number_format_i18n($average_rating, 1)); ?></span>
                </div>
            <?php endif; ?>

            <div class="product-card__actions">
                <?php if ($can_quick_buy) : ?>
                    <a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
                        class="sw-btn sw-btn--outline sw-btn--sm product-card__add-to-cart add_to_cart_button ajax_add_to_cart"
                        data-product_id="<?php echo esc_attr($product_id); ?>"
                        data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                        data-quantity="1"
                        aria-label="<?php echo esc_attr(sprintf('افزودن «%s» به سبد خرید', $title)); ?>"
                        rel="nofollow">افزودن به سبد</a>
                    <a href="<?php echo esc_url(add_query_arg('add-to-cart', $product_id, wc_get_checkout_url())); ?>"
                        class="sw-btn sw-btn--primary sw-btn--sm product-card__buy"
                        rel="nofollow">خرید</a>
                <?php else : ?>
                    <a href="<?php echo esc_url($permalink); ?>" class="sw-btn sw-btn--soft sw-btn--sm sw-btn--block product-card__view">
                        <?php
                        if ($contact_mode) {
                            echo 'تماس برای خرید';
                        } elseif (!$is_available) {
                            echo 'مشاهده محصول';
                        } elseif ($price_on_request) {
                            echo 'مشاهده و استعلام قیمت';
                        } else {
                            echo 'انتخاب و خرید';
                        }
                        ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
