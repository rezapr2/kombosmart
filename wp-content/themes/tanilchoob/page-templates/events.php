<?php

/**
 * Template Name: Events
 * Description: A custom Events page template.
 * @package TanilChoob
 */


get_header(); 

// woocommerce_breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}


$page_id = get_queried_object_id();

$end_off_sale = get_field('end_off_sale', $page_id);
$off_products = get_field('off_products', $page_id);
$see_all_url = get_field('see_all_url', $page_id);
$customers_commetns = get_field('customers_commetns', $page_id);
?>


<div class="home-page wrapper">
    <?php
    $page_id = get_the_ID();

    /* Hero Slider */
    get_template_part('template-parts/home/hero_slider');


    if( isset($end_off_sale) && $end_off_sale ): ?>
    <div class="container">
        <div class=" countdown_wrapper flex flex-col gap-30 justify-center items-center">
            <div class="md:yekan-30 bold color-black">زمان باقی مانده تا پایان جشنواره:</div>
            <div class="countdown-timer regular" data-end-date="<?php echo esc_attr($end_off_sale); ?>">
                <div class="countdown-timer__time flex gap-20 w-full h-100 justify-evenly">
							<div class="countdown-timer__item flex flex-col text-center item-center">
								<div class="countdown-timer__seconds yekan-18 md:yekan-30">00</div>
								<div class="countdown-timer__label yekan-12 md:yekan-20">ثانیه</div>
							</div>
							<div class="countdown-timer__item flex flex-col text-center item-center">
								<div class="countdown-timer__minutes yekan-18 md:yekan-30">00</div>
								<div class="countdown-timer__label yekan-12 md:yekan-20">دقیقه</div>
							</div>
							<div class="countdown-timer__item flex flex-col text-center item-center">
								<div class="countdown-timer__hours yekan-18 md:yekan-30">00</div>
								<div class="countdown-timer__label yekan-12 md:yekan-20">ساعت</div>
							</div>
							<div class="countdown-timer__item flex flex-col text-center item-center">
								<div class="countdown-timer__days yekan-18 md:yekan-30">00</div>
								<div class="countdown-timer__label yekan-12 md:yekan-20">روز</div>
							</div>
						</div>
            </div>
        </div>
    </div>

    <?php endif; ?>
        <div class="container">
            <div class="events_description flex flex-col items-center gap-04">
                <div class="color-primary yekan-28 text-center">
                    رویدادهای خاص تانیل چوب درمناسب های مختلف
                </div>
                <div class="yekan-20 color-black-60 text-center description">
                    ما در تانیل چوب در مناسبت هایی مانند عید نوروز ، بلک فردایدی و اعیاد مذهبی ، تخفیف هایی باورنکردنی روی محصولات خاص ارائه می دهیم
                </div>
            </div>
        </div>
    <?php
    if(isset($off_products) && $off_products): ?>
    <div class="container off_products flex flex-col items-center">
        <div class="title yekan-30 bold text-center color-black">محصولات <span>تخفیف</span> خورده</div>
        <div class="products grid grid-cols-1 md:grid-cols-4 ">
            <?php 

                $off_product_ids = array_map('absint', (array) $off_products);
                $args = [
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => count($off_product_ids),
                    'post__in' => $off_product_ids,
                    'orderby' => 'post__in',
                ];
                $query = new WP_Query($args);

                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                    get_template_part('template-parts/cards/product-card', null, ['post_id' => get_the_ID()]);
                    endwhile;
                    wp_reset_postdata();
                endif;
            ?>
        </div>
        <?php if(isset($see_all_url['url']) && $see_all_url):?>
            <a href="<?php echo esc_url($see_all_url['url']); ?>" class="button see_all color-primary yekan-18"><?php echo $see_all_url['title']; ?></a>
        <?php endif; ?>
    </div>
    <?php endif;
    /* Features */
    get_template_part('template-parts/home/features');

    if(isset($customers_commetns) && $customers_commetns): ?>
        <div class="container customers_commetns_wrapper flex flex-col">
            <div class="title yekan-30 bold text-center color-primary">نظرات مشتریان</div>
            <div class="customers_commetns grid grid-cols-1 md:grid-cols-2">
                <?php foreach($customers_commetns as $customer): ?>
                    <div class="comment flex flex-col gap-15">
                        <div class="name yekan-20 color-black-50"><?php echo isset($customer['name']) ? $customer['name'] : ''; ?></div>
                        <div class="comment_text yekan-24 color-black-80"><?php echo isset($customer['comment_text']) ? $customer['comment_text'] : ''; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
