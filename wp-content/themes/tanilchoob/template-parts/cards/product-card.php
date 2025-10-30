<?php

/**
 * Product Card Template for WooCommerce
 * 
 * @package TanilChoob
 */

use TanilChoob\Theme\Helper;

// Get product data if not provided
$product_id = isset($args['product_id']) ? $args['product_id'] : get_the_ID();
$product = wc_get_product($product_id);

if (!$product) {
    return;
}

// Get product data
$title = $product->get_name();
$permalink = $product->get_permalink();
$image_id = $product->get_image_id();
$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : wc_placeholder_img_src('medium');
$regular_price = $product->get_regular_price();
$sale_price = $product->get_sale_price();
$price_html = $product->get_price_html();
$average_rating = $product->get_average_rating();
$rating_count = $product->get_rating_count();
$on_sale = $product->is_on_sale();

// Calculate discount percentage if on sale
$discount_percentage = 0;
if ($on_sale && $regular_price > 0) {
    $discount_percentage = round(($regular_price - $sale_price) / $regular_price * 100);
}

// Add to cart text
$add_to_cart_text = isset($args['add_to_cart_text']) ? $args['add_to_cart_text'] : 'اضافه به سبد خرید';
?>

<div class="product-card swiper-slide transition h-100">
    <div class="product-card__inner flex flex-col h-100">
        <div class="product-card__image overflow-hidden">
            <?php if ($on_sale && $discount_percentage > 0): ?>
                <div class="product-card__discount">
                    <span><?php echo esc_html($discount_percentage . '%'); ?></span>
                </div>
            <?php endif; ?>
            <a href="<?php echo esc_url($permalink); ?>">
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
            </a>
        </div>
        <div class="product-card__content flex flex-col justify-between">
            <h3 class="product-card__title">
                <a class="yekan-20 regular color-black-80" href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
            </h3>
            <div class="flex justify-between">
                <div class="product-card flex">

                    <?php if ($average_rating > 0): ?>
                        <?php
                        $stars_html = '';
                        $rating = round($average_rating * 2) / 2; // Round to nearest 0.5

                        for ($i = 1; $i <= 5; $i++) {
                            if ($rating >= $i) {
                                // Full star
                                $stars_html .= '<span class="star star--full"></span>';
                            } elseif ($rating >= $i - 0.5) {
                                // Half star
                                $stars_html .= '<span class="star star--half"></span>';
                            } else {
                                // Empty star
                                $stars_html .= '<span class="star star--empty"></span>';
                            }
                        }

                        echo $stars_html;
                        ?>

                    <?php endif; ?>
                </div>
                <div class="product-card__price yekan-22 bold flex flex-col-reverse items-end color-primary self-end relative">
                    <?php echo $price_html; ?>
                </div>
            </div>


            <div class="product-card__actions">
                <a href="<?php echo esc_url($permalink); ?>" class="product-card__add-to-cart flex items-center justify-center gap-10">
                    <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.16406 1.6665H3.61407C4.51407 1.6665 5.2224 2.4415 5.1474 3.33317L4.45573 11.6332C4.33906 12.9915 5.41406 14.1582 6.78072 14.1582H15.6557C16.8557 14.1582 17.9057 13.1748 17.9974 11.9832L18.4474 5.73317C18.5474 4.34984 17.4974 3.22483 16.1057 3.22483H5.3474" stroke="black" stroke-opacity="0.8" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M14.0417 18.3333C14.617 18.3333 15.0833 17.867 15.0833 17.2917C15.0833 16.7164 14.617 16.25 14.0417 16.25C13.4664 16.25 13 16.7164 13 17.2917C13 17.867 13.4664 18.3333 14.0417 18.3333Z" stroke="black" stroke-opacity="0.8" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M7.3776 18.3333C7.9529 18.3333 8.41927 17.867 8.41927 17.2917C8.41927 16.7164 7.9529 16.25 7.3776 16.25C6.80231 16.25 6.33594 16.7164 6.33594 17.2917C6.33594 17.867 6.80231 18.3333 7.3776 18.3333Z" stroke="black" stroke-opacity="0.8" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M8 6.6665H18" stroke="black" stroke-opacity="0.8" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="yekan-16 color-black-80"><?php echo esc_html($add_to_cart_text); ?></span>
                </a>
            </div>
        </div>
    </div>
</div>