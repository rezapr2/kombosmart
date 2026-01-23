<?php

/**
 * Template Name: Availability And Shipping Method
 * Description: A custom Availability And Shipping Method page template
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

get_header();

// woocommerce_breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}


$page_id = get_queried_object_id();
$types_product_inventory_status = get_field('types_product_inventory_status', $page_id);
$types_productshipping_delivery_methods = get_field('types_productshipping_delivery_methods', $page_id);
?>
<div id="page-availability-shipping-method" class="page-template-availability-shipping-method ">
    <section class="hero container flex flex-col gap-10 items-center">
        <h1 class="title color-primary bold relative"><?php the_title(); ?></h1>
        <div class="hero_subtitle color-black-70 yekan-12 md:yekan-20 text-center"><?php echo get_field('hero_subtitle', $page_id) ?: ''; ?></div>
    </section>
    <?php if ($types_product_inventory_status): ?>
        <section class="inventory-status_wrapper container mt-30">
            <div class="inventory-status_title yekan-16 md:yekan-30 flex items-center">انواع وضعيت موجودى محصولات</div>
            <div class="inventory-status_list grid grid-cols-1 md:grid-cols-2 mt-30">
            <?php foreach ($types_product_inventory_status as $item): ?>
                <div class="inventory-status_item flex flex-col items-start gap-10">
                    <div class="inventory-status_item_title yekan-14 md:yekan-20 text-center <?php echo $item['hex_color'] ?: ''; ?>"><?php echo $item['title'] ?: ''; ?></div>
                    <div class="inventory-status_item_description yekan-12 md:yekan-20 color-black-70"><?php echo $item['description'] ?: ''; ?></div>
                </div>
            <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    <?php if ($types_productshipping_delivery_methods): ?>
        <section class="delivery_methods container mt-30">
            <div class="delivery_methods_title yekan-16 md:yekan-30 flex items-center">نحوه ارسال و تحویل محصولات</div>
            <div class="delivery_methods_list flex flex-col gap-10 mt-30">
            <?php foreach ($types_productshipping_delivery_methods as $item): ?>
                <div class="delivery_methods_item flex flex-col md:flex-row items-center gap-07 md:gap-15">
                    <div class="delivery_methods_item_title yekan-16 md:yekan-20 text-center color-black-80 bg-black-05"><?php echo $item['title'] ?: ''; ?></div>
                    <div class="delivery_methods_item_description yekan-14 md:yekan-18 color-black-70 text-center md:text-right "><?php echo $item['description'] ?: ''; ?></div>
                </div>
            <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */