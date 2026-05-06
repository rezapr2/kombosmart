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
                <div class="item circle-radius active">
                    <div class="circle"></div>
                    <span class="label">سبد خرید</span>
                </div>
                <div class="item circle-radius">
                    <div class="circle"></div>
                    <span class="label">مشخصات و آدرس</span>
                </div>
                <div class="item circle-radius">
                    <div class="circle"></div>
                    <span class="label">شیوه پرداخت</span>
                </div>
                <div class="item circle-radius">
                    <div class="circle"></div>
                    <span class="label">پیش فاکتور</span>
                </div>
            </div>
        </div>
        <div class="page-content yekan-16 md:yekan-20 color-black-70">
            <?php while (have_posts()) : the_post(); ?>
                <?php the_content(); ?>
            <?php endwhile; ?>
        </div>
    </section>
</div>
<?php
get_footer();
