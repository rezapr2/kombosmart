<?php

/**
 * Template Name: Faqs
 * Description: A custom Faqs page template with info and form.
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

use TanilChoob\Theme\Helper;

get_header();

// woocommerce_breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}


$page_id = get_queried_object_id();
?>
<div id="page-warranty" class="page-template-warranty">
    <section class="hero container flex flex-col gap-10 items-center">
        <h1 class="title color-primary bold relative"><?php the_title(); ?></h1>
        <div class="hero_subtitle color-black-70 yekan-12 md:yekan-20 text-center"><?php echo get_field('hero_subtitle', $page_id) ?: ''; ?>
        </div>
    </section>
    <?php
    $faqs = get_field('faqs', $page_id) ?: [];
    if ($faqs):
        ?>
        <section class="container flex flex-col gap-20">
            <h2 class="page_blocks_title text-center yekan-18 md:yekan-28 bold color-primary mt-20 md:mt-40">
                سوالات متداول 
            </h2>

            <div class="list_faqs flex flex-col gap-20">
                <?php foreach ($faqs as $faq): ?>
                    <div class="faq-item slide-down-wrapper flex flex-col gap-04 md:gap-20">
                        <div
                            class="faq-question slide-down-trigger  flex justify-between items-center cursor-pointer">
                            <h3 class="regular yekan-14 md:yekan-24 color-black-80"><?php echo $faq['question']; ?></h3>
                        </div>
                        <div class="faq-answer slide-down-content yekan-12 md:yekan-20 text-center md:text-right color-black-70" style="display: none;">
                            <p><?php echo $faq['answer']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>


</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */
