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
$add_to_cart_text = isset($args['add_to_cart_text']) ? $args['add_to_cart_text'] : 'افزودن به سبد خرید';
?>

<div class="product-card swiper-slide">
    <div class="product-card__inner">
        <div class="product-card__image">
            <?php if ($on_sale && $discount_percentage > 0): ?>
            <div class="product-card__discount">
                <span><?php echo esc_html($discount_percentage . '%'); ?></span>
            </div>
            <?php endif; ?>
            <a href="<?php echo esc_url($permalink); ?>">
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
            </a>
        </div>
        <div class="product-card__content">
            <h3 class="product-card__title yekan-16 regular color-black-80">
                <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
            </h3>
            
            <?php if ($average_rating > 0): ?>
            <div class="product-card__rating">
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
            </div>
            <?php endif; ?>
            
            <div class="product-card__price yekan-16 bold">
                <?php echo $price_html; ?>
            </div>
            
            <div class="product-card__actions">
                <a href="<?php echo esc_url($permalink); ?>" class="product-card__add-to-cart yekan-14">
                    <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/cart.svg')); ?>
                    <span><?php echo esc_html($add_to_cart_text); ?></span>
                </a>
            </div>
        </div>
    </div>
</div>