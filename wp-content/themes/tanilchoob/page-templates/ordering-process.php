<?php

/**
 * Template Name: Ordering Process
 * Description: A custom page template without header.
 * @package TanilChoob
 */


defined('ABSPATH') || exit;

get_header();

if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}
$page_id = get_queried_object_id();

?>
<div id="page-ordering-process" class="page-template-ordering-process">
    <section class="container">
        <div class="header relative">
            <div class="top-bg bg-black-03 w-full absolute"></div>
            <div class="items flex justify-between relative z-index-1">
                <div class="flex flex-col items-center gap-20 item  active">
                    <div class="circle circle-radius"> <img
                            src="<?php echo get_template_directory_uri(); ?>/assets/frontend/dist/images/icon_cart.png"
                            alt="Cart">
                    </div>
                    <span class="label yekan-24 color-black">سبد خرید</span>
                </div>
                <div class="flex flex-col items-center gap-20 item circle-radius">
                    <div class="circle circle-radius"><img
                            src="<?php echo get_template_directory_uri(); ?>/assets/frontend/dist/images/icon_location.png"
                            alt="Cart"></div>
                    <span class="label yekan-24 color-black">مشخصات و آدرس</span>
                </div>
                <div class="flex flex-col items-center gap-20 item circle-radius">
                    <div class="circle circle-radius"><img
                            src="<?php echo get_template_directory_uri(); ?>/assets/frontend/dist/images/icon_paymentmethode.png"
                            alt="Cart"></div>
                    <span class="label yekan-24 color-black">شیوه پرداخت</span>
                </div>
                <div class="flex flex-col items-center gap-20 item circle-radius">
                    <div class="circle circle-radius"><img
                            src="<?php echo get_template_directory_uri(); ?>/assets/frontend/dist/images/icon_invoice.png"
                            alt="Cart"></div>
                    <span class="label yekan-24 color-black">پیش فاکتور</span>
                </div>
            </div>
        </div>
        <div class="page-content yekan-16 md:yekan-20 color-black-70">
            <?php while (have_posts()):
                the_post(); ?>
                <?php the_content(); ?>
            <?php endwhile; ?>
        </div>
    </section>
</div>
<?php
get_footer();
