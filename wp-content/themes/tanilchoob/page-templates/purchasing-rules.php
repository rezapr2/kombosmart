<?php

/**
 * Template Name: Purchasing Rules
 * Description: A custom Purchasing Rules page template
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

get_header();

// woocommerce_breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}


$page_id = get_queried_object_id();
$content_boxes = get_field('content_boxes', $page_id);
?>
<div id="page-purchasing-rules" class="page-template-purchasing-rules ">
    <section class="hero container flex flex-col gap-10 items-center">
        <h1 class="title color-primary bold relative"><?php the_title(); ?></h1>
        <div class="hero_subtitle color-black-70 yekan-12 md:yekan-20 text-center"><?php echo get_field('hero_subtitle', $page_id) ?: ''; ?></div>
    </section>
    <?php if ($content_boxes): ?>
        <section class="content_boxes_wrapper container grid grid-cols-1 md:grid-cols-2 gap-10 mt-30">
            <?php foreach ($content_boxes as $item): ?>
                <div class="content_box flex flex-col items-center md:items-start gap-07 bg-black-03">
                    <div class="content_box_title yekan-18 md:yekan-24 text-center md:text-right color-primary bold"><?php echo $item['title'] ?: ''; ?></div>
                    <div class="content_box_content yekan-14 md:yekan-20 text-center md:text-right color-black"><?php echo $item['content'] ?: ''; ?></div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    <div class="color-primary text-center bold yekan-14 md:yekan-24 mt-40">ثبت هرگونه سفارش به منزله پذيرش كامل قوانين فوق است.</div>
</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */